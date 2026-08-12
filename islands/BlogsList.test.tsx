// @vitest-environment jsdom
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { cleanup, render, screen, waitFor, within } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import BlogsList from "./BlogsList";
import { TOAST_EVENT } from "@tracht-digital-solutions/tds-shared/toast";

/**
 * The blog-CMS island, driven through the real UI. `globals` is off in the
 * vitest config, so cleanup is explicit.
 *
 * Every request goes through a fetch stub that answers by URL + method, which
 * lets each test assert the exact call the backend would receive — the payload
 * shape is a contract with the PHP module in `php/`.
 */

type Handler = (url: string, init?: RequestInit) => { status?: number; body?: unknown } | undefined;

let handlers: Handler[] = [];
let calls: Array<{ url: string; method: string; body: unknown }> = [];

/** Register a canned answer; later registrations win over earlier ones. */

/**
 * Path + query of a request. The island calls an ABSOLUTE URL now (via
 * `apiFetch`); a relative one would hit the product's own static host and come
 * back as SPA-fallback HTML with a 200. Matching on the path keeps the route
 * matchers below anchored.
 */
const pathOf = (url: string) => String(url).replace(/^https?:\/\/[^/]+/i, "");

function respond(match: RegExp, body: unknown, status = 200, method?: string) {
  handlers.unshift((url, init) => {
    if (!match.test(pathOf(url))) return undefined;
    if (method && (init?.method ?? "GET") !== method) return undefined;
    return { status, body };
  });
}

/** Outcomes are toasts now — collected off the `tds:toast` bus. */
let toasts: Array<{ variant: string; message: string }> = [];
const collectToast = (e: Event) => {
  toasts.push((e as CustomEvent<{ variant: string; message: string }>).detail);
};

beforeEach(() => {
  toasts = [];
  window.addEventListener(TOAST_EVENT, collectToast);
  handlers = [];
  calls = [];
  vi.stubGlobal(
    "fetch",
    vi.fn(async (url: string, init?: RequestInit) => {
      calls.push({
        url,
        method: init?.method ?? "GET",
        body: typeof init?.body === "string" ? JSON.parse(init.body) : undefined,
      });
      for (const h of handlers) {
        const hit = h(url, init);
        if (hit) {
          const status = hit.status ?? 200;
          return {
            ok: status >= 200 && status < 300,
            status,
            json: async () => hit.body ?? {},
          } as Response;
        }
      }
      return { ok: true, status: 200, json: async () => ({}) } as Response;
    }),
  );
});

afterEach(() => {
  window.removeEventListener(TOAST_EVENT, collectToast);
  cleanup();
});

const user = () => userEvent.setup({ delay: null });
const BLOG = { id: 1, blog_key: "haupt", name: "Hauptblog" };

/** Wait past the mount effect so the list has settled. */
async function renderList(blogs: unknown[] = [BLOG]) {
  respond(/\/blogs$/, { blogs });
  render(<BlogsList />);
  await waitFor(() => expect(calls.some((c) => pathOf(c.url) === "/blogs")).toBe(true));
}

describe("loading the blog list", () => {
  it("requests the blog list on mount, with credentials", async () => {
    await renderList();
    await screen.findByText("Hauptblog");
    const fetchMock = fetch as unknown as ReturnType<typeof vi.fn>;
    expect(fetchMock.mock.calls[0]![1]).toMatchObject({ credentials: "include" });
  });

  it("renders each blog's name and key", async () => {
    await renderList([BLOG, { id: 2, blog_key: "zweit", name: "Zweitblog" }]);
    expect(await screen.findByText("Hauptblog")).toBeTruthy();
    expect(screen.getByText("zweit")).toBeTruthy();
  });

  it("shows the empty state when there are no blogs", async () => {
    await renderList([]);
    expect(await screen.findByText("Noch keine Blogs angelegt.")).toBeTruthy();
  });

  it("degrades to the empty state when the request fails", async () => {
    // A 500 must not leave the island stuck on its loading branch forever.
    respond(/\/blogs$/, {}, 500);
    render(<BlogsList />);
    expect(await screen.findByText("Noch keine Blogs angelegt.")).toBeTruthy();
  });

  it("degrades to the empty state when fetch rejects outright", async () => {
    vi.stubGlobal("fetch", vi.fn(async () => { throw new TypeError("offline"); }));
    render(<BlogsList />);
    expect(await screen.findByText("Noch keine Blogs angelegt.")).toBeTruthy();
  });

  it("tolerates a response with no blogs field", async () => {
    respond(/\/blogs$/, {});
    render(<BlogsList />);
    expect(await screen.findByText("Noch keine Blogs angelegt.")).toBeTruthy();
  });
});

describe("creating a blog", () => {
  async function submit(key: string, name: string) {
    await renderList([]);
    const u = user();
    if (key) await u.type(screen.getByPlaceholderText("blog-key (kebab)"), key);
    if (name) await u.type(screen.getByPlaceholderText("Name"), name);
    await u.click(screen.getByRole("button", { name: "Blog hinzufügen" }));
    return calls.filter((c) => c.method === "POST");
  }

  it("posts a valid key and name", async () => {
    const posts = await submit("mein-blog", "Mein Blog");
    expect(posts).toHaveLength(1);
    expect(pathOf(posts[0]!.url)).toBe("/blogs");
    expect(posts[0]!.body).toEqual({ blog_key: "mein-blog", name: "Mein Blog" });
  });

  it("rejects a key with uppercase or spaces before sending", async () => {
    // The backend enforces the same shape; catching it here avoids a 422.
    expect(await submit("Mein Blog", "Name")).toHaveLength(0);
  });

  it("rejects a key shorter than two characters", async () => {
    expect(await submit("a", "Name")).toHaveLength(0);
  });

  it("accepts a two-character key (the boundary)", async () => {
    expect(await submit("ab", "Name")).toHaveLength(1);
  });

  it("rejects a key longer than 64 characters", async () => {
    expect(await submit("a".repeat(65), "Name")).toHaveLength(0);
  });

  it("accepts a 64-character key (the boundary)", async () => {
    expect(await submit("a".repeat(64), "Name")).toHaveLength(1);
  });

  it("rejects a whitespace-only name", async () => {
    expect(await submit("gueltig", "   ")).toHaveLength(0);
  });

  it("rejects an underscore in the key", async () => {
    expect(await submit("mein_blog", "Name")).toHaveLength(0);
  });

  it("reloads the list and clears the form after a successful create", async () => {
    await renderList([]);
    const u = user();
    await u.type(screen.getByPlaceholderText("blog-key (kebab)"), "neu");
    await u.type(screen.getByPlaceholderText("Name"), "Neu");
    await u.click(screen.getByRole("button", { name: "Blog hinzufügen" }));

    await waitFor(() => expect(calls.filter((c) => pathOf(c.url) === "/blogs" && c.method === "GET")).toHaveLength(2));
    expect((screen.getByPlaceholderText("blog-key (kebab)") as HTMLInputElement).value).toBe("");
    expect((screen.getByPlaceholderText("Name") as HTMLInputElement).value).toBe("");
  });

  it("keeps the form filled when the create fails", async () => {
    // Losing the input on a server error would make the user retype it.
    await renderList([]);
    respond(/\/blogs$/, {}, 500, "POST");
    const u = user();
    await u.type(screen.getByPlaceholderText("blog-key (kebab)"), "neu");
    await u.type(screen.getByPlaceholderText("Name"), "Neu");
    await u.click(screen.getByRole("button", { name: "Blog hinzufügen" }));

    await waitFor(() => expect(calls.some((c) => c.method === "POST")).toBe(true));
    expect((screen.getByPlaceholderText("blog-key (kebab)") as HTMLInputElement).value).toBe("neu");
  });
});

describe("opening a blog", () => {
  async function openBlog() {
    await renderList();
    respond(/\/blogs\/haupt\/posts$/, { posts: [] });
    respond(/\/blog\/authors$/, { authors: [] });
    await user().click(await screen.findByRole("button", { name: /Hauptblog/ }));
    return user();
  }

  it("loads that blog's posts and the author list", async () => {
    await openBlog();
    await waitFor(() => {
      expect(calls.some((c) => pathOf(c.url) === "/blogs/haupt/posts")).toBe(true);
      expect(calls.some((c) => pathOf(c.url) === "/blog/authors")).toBe(true);
    });
  });

  it("scopes the post request to the selected blog's key", async () => {
    await renderList([BLOG, { id: 2, blog_key: "zweit", name: "Zweitblog" }]);
    respond(/\/blogs\/zweit\/posts$/, { posts: [] });
    respond(/\/blog\/authors$/, { authors: [] });
    await user().click(await screen.findByRole("button", { name: /Zweitblog/ }));
    await waitFor(() => expect(calls.some((c) => pathOf(c.url) === "/blogs/zweit/posts")).toBe(true));
    expect(calls.some((c) => pathOf(c.url) === "/blogs/haupt/posts")).toBe(false);
  });

  it("returns to the blog list", async () => {
    const u = await openBlog();
    await u.click(await screen.findByRole("button", { name: "← Blogs" }));
    expect(await screen.findByPlaceholderText("blog-key (kebab)")).toBeTruthy();
  });
});

describe("the post editor", () => {
  /** Open a blog and start a new post. */
  async function newPost() {
    await renderList();
    respond(/\/blogs\/haupt\/posts$/, { posts: [] });
    respond(/\/blog\/authors$/, { authors: [{ id: 7, name: "Julian" }] });
    const u = user();
    await u.click(await screen.findByRole("button", { name: /Hauptblog/ }));
    await u.click(await screen.findByRole("button", { name: "Neuer Beitrag" }));
    return u;
  }

  const puts = () => calls.filter((c) => c.method === "PUT");

  it("refuses to save without a kebab-case slug", async () => {
    const u = await newPost();
    await u.type(screen.getByPlaceholderText("mein-beitrag"), "Kein Slug!");
    await u.click(screen.getByRole("button", { name: "Speichern" }));
    expect(await screen.findByText(/Slug muss kebab-case sein/)).toBeTruthy();
    expect(puts()).toHaveLength(0);
  });

  it("refuses to save without a title", async () => {
    const u = await newPost();
    await u.type(screen.getByPlaceholderText("mein-beitrag"), "gueltig");
    await u.type(screen.getByPlaceholderText(/Text in Markdown/), "Inhalt");
    await u.click(screen.getByRole("button", { name: "Speichern" }));
    expect(await screen.findByText(/Titel und Inhalt sind erforderlich/)).toBeTruthy();
    expect(puts()).toHaveLength(0);
  });

  it("refuses to save without a body", async () => {
    const u = await newPost();
    await u.type(screen.getByPlaceholderText("mein-beitrag"), "gueltig");
    await u.type(screen.getByPlaceholderText("Titel des Beitrags"), "Titel");
    await u.click(screen.getByRole("button", { name: "Speichern" }));
    expect(await screen.findByText(/Titel und Inhalt sind erforderlich/)).toBeTruthy();
    expect(puts()).toHaveLength(0);
  });

  it("treats a whitespace-only title as missing", async () => {
    const u = await newPost();
    await u.type(screen.getByPlaceholderText("mein-beitrag"), "gueltig");
    await u.type(screen.getByPlaceholderText("Titel des Beitrags"), "   ");
    await u.type(screen.getByPlaceholderText(/Text in Markdown/), "Inhalt");
    await u.click(screen.getByRole("button", { name: "Speichern" }));
    expect(await screen.findByText(/Titel und Inhalt sind erforderlich/)).toBeTruthy();
  });

  it("PUTs to the slug-scoped endpoint with a trimmed payload", async () => {
    const u = await newPost();
    await u.type(screen.getByPlaceholderText("mein-beitrag"), "mein-beitrag");
    await u.type(screen.getByPlaceholderText("Titel des Beitrags"), "  Titel  ");
    await u.type(screen.getByPlaceholderText("Kurzbeschreibung (optional)"), "  Auszug  ");
    await u.type(screen.getByPlaceholderText(/Text in Markdown/), "Inhalt");
    await u.click(screen.getByRole("button", { name: "Speichern" }));

    await waitFor(() => expect(puts()).toHaveLength(1));
    expect(pathOf(puts()[0]!.url)).toBe("/blogs/haupt/posts/mein-beitrag");
    expect(puts()[0]!.body).toMatchObject({
      lang: "de",
      title: "Titel",
      excerpt: "Auszug",
      body: "Inhalt",
      draft: true,
    });
  });

  it("does not trim the body — leading indentation is meaningful in markdown", async () => {
    const u = await newPost();
    await u.type(screen.getByPlaceholderText("mein-beitrag"), "slug");
    await u.type(screen.getByPlaceholderText("Titel des Beitrags"), "T");
    await u.type(screen.getByPlaceholderText(/Text in Markdown/), "  eingerueckt");
    await u.click(screen.getByRole("button", { name: "Speichern" }));
    await waitFor(() => expect(puts()).toHaveLength(1));
    expect((puts()[0]!.body as { body: string }).body).toBe("  eingerueckt");
  });

  it("defaults an empty category to allgemein", async () => {
    const u = await newPost();
    await u.clear(screen.getByPlaceholderText("allgemein"));
    await u.type(screen.getByPlaceholderText("mein-beitrag"), "slug");
    await u.type(screen.getByPlaceholderText("Titel des Beitrags"), "T");
    await u.type(screen.getByPlaceholderText(/Text in Markdown/), "B");
    await u.click(screen.getByRole("button", { name: "Speichern" }));
    await waitFor(() => expect(puts()).toHaveLength(1));
    expect((puts()[0]!.body as { category: string }).category).toBe("allgemein");
  });

  it("sends draft:false once publish is ticked", async () => {
    const u = await newPost();
    await u.type(screen.getByPlaceholderText("mein-beitrag"), "slug");
    await u.type(screen.getByPlaceholderText("Titel des Beitrags"), "T");
    await u.type(screen.getByPlaceholderText(/Text in Markdown/), "B");
    await u.click(screen.getByRole("checkbox"));
    await u.click(screen.getByRole("button", { name: "Speichern" }));
    await waitFor(() => expect(puts()).toHaveLength(1));
    expect((puts()[0]!.body as { draft: boolean }).draft).toBe(false);
  });

  it("sends the chosen author id as a number", async () => {
    const u = await newPost();
    await u.type(screen.getByPlaceholderText("mein-beitrag"), "slug");
    await u.type(screen.getByPlaceholderText("Titel des Beitrags"), "T");
    await u.type(screen.getByPlaceholderText(/Text in Markdown/), "B");
    await u.selectOptions(screen.getByRole("combobox", { name: /Autor/ }), "7");
    await u.click(screen.getByRole("button", { name: "Speichern" }));
    await waitFor(() => expect(puts()).toHaveLength(1));
    expect((puts()[0]!.body as { author_id: unknown }).author_id).toBe(7);
  });

  it("reports the HTTP status when the save fails", async () => {
    const u = await newPost();
    respond(/\/posts\/slug$/, {}, 409, "PUT");
    await u.type(screen.getByPlaceholderText("mein-beitrag"), "slug");
    await u.type(screen.getByPlaceholderText("Titel des Beitrags"), "T");
    await u.type(screen.getByPlaceholderText(/Text in Markdown/), "B");
    await u.click(screen.getByRole("button", { name: "Speichern" }));
await waitFor(() => expect(toasts.some((t) => t.variant === "danger" && t.message.includes("409"))).toBe(true));
  });

  it("returns to the post list after a successful save", async () => {
    const u = await newPost();
    await u.type(screen.getByPlaceholderText("mein-beitrag"), "slug");
    await u.type(screen.getByPlaceholderText("Titel des Beitrags"), "T");
    await u.type(screen.getByPlaceholderText(/Text in Markdown/), "B");
    await u.click(screen.getByRole("button", { name: "Speichern" }));
    expect(await screen.findByRole("button", { name: "Neuer Beitrag" })).toBeTruthy();
  });

  it("offers no delete button for a post that does not exist yet", async () => {
    await newPost();
    expect(screen.queryByRole("button", { name: "Löschen" })).toBeNull();
  });

  it("leaves the slug and language editable for a new post", async () => {
    await newPost();
    expect((screen.getByPlaceholderText("mein-beitrag") as HTMLInputElement).disabled).toBe(false);
  });
});

describe("the editor's markdown preview", () => {
  async function typeBody(body: string) {
    await renderList();
    respond(/\/blogs\/haupt\/posts$/, { posts: [] });
    respond(/\/blog\/authors$/, { authors: [] });
    const u = user();
    await u.click(await screen.findByRole("button", { name: /Hauptblog/ }));
    await u.click(await screen.findByRole("button", { name: "Neuer Beitrag" }));
    await u.type(screen.getByPlaceholderText(/Text in Markdown/), body);
    await u.click(screen.getByRole("button", { name: "Vorschau" }));
    return u;
  }

  it("renders markdown as HTML in the preview", async () => {
    await typeBody("# Titel");
    expect(await screen.findByRole("heading", { level: 1, name: "Titel" })).toBeTruthy();
  });

  it("does not execute HTML pasted into the body", async () => {
    // The preview is the one place admin markdown reaches innerHTML.
    await typeBody("<img src=x onerror=alert(1)>");
    expect(document.querySelector("img")).toBeNull();
    expect(screen.getByText(/<img src=x onerror=alert\(1\)>/)).toBeTruthy();
  });

  it("toggles back to the textarea, preserving the body", async () => {
    const u = await typeBody("bleibt");
    await u.click(screen.getByRole("button", { name: "Bearbeiten" }));
    expect((screen.getByPlaceholderText(/Text in Markdown/) as HTMLTextAreaElement).value).toBe("bleibt");
  });
});

describe("existing posts", () => {
  const POST = { slug: "hallo", lang: "de", title: "Hallo", draft: 0, published_at: "2026-01-01" };

  async function openPost() {
    await renderList();
    respond(/\/blogs\/haupt\/posts$/, { posts: [POST] });
    respond(/\/blog\/authors$/, { authors: [] });
    respond(/\/posts\/hallo\?lang=de$/, { title: "Hallo", body: "Text", category: "news", draft: 0 });
    const u = user();
    await u.click(await screen.findByRole("button", { name: /Hauptblog/ }));
    await u.click(await screen.findByRole("button", { name: /Hallo/ }));
    return u;
  }

  it("lists an existing post", async () => {
    await renderList();
    respond(/\/blogs\/haupt\/posts$/, { posts: [POST] });
    respond(/\/blog\/authors$/, { authors: [] });
    await user().click(await screen.findByRole("button", { name: /Hauptblog/ }));
    expect(await screen.findByRole("button", { name: /Hallo/ })).toBeTruthy();
  });

  it("loads the full post body when opened", async () => {
    await openPost();
    await waitFor(() =>
      expect((screen.getByPlaceholderText(/Text in Markdown/) as HTMLTextAreaElement).value).toBe("Text"),
    );
  });

  it("locks the slug and language of an existing post", async () => {
    // Editing either would orphan the row rather than rename it.
    await openPost();
    await waitFor(() =>
      expect((screen.getByPlaceholderText("mein-beitrag") as HTMLInputElement).disabled).toBe(true),
    );
  });

  it("deletes with the language as a query parameter", async () => {
    const u = await openPost();
    await u.click(await screen.findByRole("button", { name: "Löschen" }));
    // The post delete is gated by <ConfirmDialog> now (it used to wipe a post
    // on a single click); confirming is the last matching button.
    await u.click(screen.getAllByRole("button", { name: /Löschen/ }).at(-1)!);
    await waitFor(() => {
      const del = calls.find((c) => c.method === "DELETE");
      expect(pathOf(del!.url)).toBe("/blogs/haupt/posts/hallo?lang=de");
    });
  });

  it("reports the status when a delete fails", async () => {
    const u = await openPost();
    respond(/\/posts\/hallo\?lang=de$/, {}, 403, "DELETE");
    await u.click(await screen.findByRole("button", { name: "Löschen" }));
    // The post delete is gated by <ConfirmDialog> now (it used to wipe a post
    // on a single click); confirming is the last matching button.
    await u.click(screen.getAllByRole("button", { name: /Löschen/ }).at(-1)!);
    await waitFor(() => expect(toasts.some((t) => t.variant === "danger" && t.message.includes("403"))).toBe(true));
  });
});

describe("rebuild and translation controls", () => {
  async function openBlog(blog: Record<string, unknown> = BLOG) {
    await renderList([blog]);
    respond(/\/blogs\/haupt\/posts$/, { posts: [] });
    respond(/\/blog\/authors$/, { authors: [] });
    await user().click(await screen.findByRole("button", { name: /Hauptblog/ }));
    return user();
  }

  it("saves the rebuild configuration trimmed", async () => {
    const u = await openBlog();
    const repo = await screen.findByPlaceholderText("Tracht-Digital-Solutions/tds-blog-frontend");
    await u.type(repo, "  Tracht-Digital-Solutions/tds-blog-frontend  ");
    await u.click(screen.getByRole("button", { name: "Konfiguration speichern" }));
    await waitFor(() => {
      const put = calls.find((c) => c.method === "PUT");
      expect(put?.body).toMatchObject({ rebuild_repo: "Tracht-Digital-Solutions/tds-blog-frontend" });
    });
  });

  it("explains a 503 from a rebuild as a missing token, not a generic error", async () => {
    const u = await openBlog();
    respond(/\/rebuild$/, {}, 503, "POST");
    await u.click(await screen.findByRole("button", { name: /Jetzt neu bauen/ }));
    expect(await screen.findByText(/Kein Rebuild-Token konfiguriert/)).toBeTruthy();
  });

  it("explains a 422 from a rebuild as a missing repository", async () => {
    const u = await openBlog();
    respond(/\/rebuild$/, {}, 422, "POST");
    await u.click(await screen.findByRole("button", { name: /Jetzt neu bauen/ }));
    expect(await screen.findByText(/kein Repository hinterlegt/)).toBeTruthy();
  });

  it("reports an unexpected rebuild failure with its status", async () => {
    const u = await openBlog();
    respond(/\/rebuild$/, {}, 500, "POST");
    await u.click(await screen.findByRole("button", { name: /Jetzt neu bauen/ }));
    await waitFor(() => expect(toasts.some((t) => t.variant === "danger" && t.message.includes("500"))).toBe(true));
  });

  it("reports the counts returned by a translation backfill", async () => {
    const u = await openBlog();
    respond(/\/translations\/backfill$/, { created: 3, skipped: 1 }, 200, "POST");
    await u.click(await screen.findByRole("button", { name: /Übersetzungen nachziehen/ }));
await waitFor(() => expect(toasts.some((t) => t.variant === "success" && /3 erstellt, 1 übersprungen/.test(t.message))).toBe(true));
  });

  it("explains a 503 from a backfill as DeepL not being configured", async () => {
    const u = await openBlog();
    respond(/\/translations\/backfill$/, {}, 503, "POST");
    await u.click(await screen.findByRole("button", { name: /Übersetzungen nachziehen/ }));
    expect(await screen.findByText(/nicht konfiguriert/)).toBeTruthy();
  });

  it("survives a backfill success with an unparseable body", async () => {
    const u = await openBlog();
    handlers.unshift((url, init) =>
      /backfill/.test(url) && init?.method === "POST" ? { status: 200, body: undefined } : undefined,
    );
    await u.click(await screen.findByRole("button", { name: /Übersetzungen nachziehen/ }));
await waitFor(() => expect(toasts.some((t) => t.variant === "success" && /0 erstellt, 0 übersprungen/.test(t.message))).toBe(true));
  });

  it("pre-fills the rebuild workflow with dev.yml when the blog has none", async () => {
    await openBlog();
    expect((await screen.findByPlaceholderText("dev.yml") as HTMLInputElement).value).toBe("dev.yml");
  });

  it("pre-fills the rebuild fields from the blog record", async () => {
    await openBlog({ ...BLOG, rebuild_repo: "o/r", rebuild_workflow: "release.yml" });
    expect((await screen.findByPlaceholderText("Tracht-Digital-Solutions/tds-blog-frontend") as HTMLInputElement).value).toBe("o/r");
    expect((screen.getByPlaceholderText("dev.yml") as HTMLInputElement).value).toBe("release.yml");
  });
});
