// @vitest-environment jsdom
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { primeRuntimeConfig } from "@tracht-digital-solutions/tds-shared/api";
import { put, resetCache } from "@tracht-digital-solutions/tds-shared/data";
import { cleanup, render, screen, waitFor, within } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import BlogsList from "./BlogsList";
import { TOAST_EVENT } from "@tracht-digital-solutions/tds-shared/toast";

/**
 * The blog-CMS CONTENT island, driven through the real UI. `globals` is off in
 * the vitest config, so cleanup is explicit.
 *
 * Every request goes through a fetch stub that answers by URL + method, which
 * lets each test assert the exact call the backend would receive — the payload
 * shape is a contract with the PHP module in `php/`.
 *
 * Adding a blog and pointing its rebuild/page cache live in Einstellungen now
 * (`BlogRegistry.test.tsx`). What is left here is writing.
 */

type Hit = { status?: number; body?: unknown };
type Handler = (url: string, init?: RequestInit) => Hit | Promise<Hit | undefined> | undefined;

let handlers: Handler[] = [];
let calls: Array<{ url: string; method: string; body: unknown }> = [];

/**
 * Path + query of a request. The island calls an ABSOLUTE URL (via `apiFetch`);
 * a relative one would hit the product's own host and come back as SPA-fallback
 * HTML with a 200. Matching on the path keeps the route matchers anchored.
 */
const pathOf = (url: string) => String(url).replace(/^https?:\/\/[^/]+/i, "");

/** Register a canned answer; later registrations win over earlier ones. */
function respond(match: RegExp, body: unknown, status = 200, method?: string) {
  handlers.unshift((url, init) => {
    if (!match.test(pathOf(url))) return undefined;
    if (method && (init?.method ?? "GET") !== method) return undefined;
    return { status, body };
  });
}

/** Outcomes are toasts — collected off the `tds:toast` bus. */
let toasts: Array<{ variant: string; message: string }> = [];
const collectToast = (e: Event) => {
  toasts.push((e as CustomEvent<{ variant: string; message: string }>).detail);
};

beforeEach(() => {
  // The data cache is module-level and survives between tests by design; a
  // leaked entry would let one test paint another's fixture.
  resetCache();
  // apiFetch consults the host-side runtime config (/tds-runtime.json) before
  // it resolves a URL, so without this the first fetch call is that probe. The
  // panel products never ship the file — they render <meta name="tds-api-base">
  // — so "absent" is also what happens in production.
  primeRuntimeConfig(null);
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
        const hit = await h(url, init);
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
  resetCache();
});

const user = () => userEvent.setup({ delay: null });
const BLOG = { id: 1, blog_key: "haupt", name: "Hauptblog", cache_url: "https://blog.example" };

/**
 * Render with a blog list answering. A single blog SELECTS ITSELF — there is no
 * list-then-drill-in step any more, because with one blog the list was a screen
 * whose only content was one button.
 */
async function renderList(blogs: unknown[] = [BLOG]) {
  respond(/\/blogs$/, { blogs }, 200, "GET");
  render(<BlogsList />);
  await waitFor(() => expect(calls.some((c) => pathOf(c.url) === "/blogs")).toBe(true));
}

/** Render, auto-select the only blog, and wait for its article list. */
async function openBlog(
  blog: Record<string, unknown> = BLOG,
  posts: unknown[] = [],
  authors: unknown[] = [],
) {
  respond(/\/blogs\/[a-z-]+\/posts$/, { posts }, 200, "GET");
  respond(/\/blog\/authors$/, { authors }, 200, "GET");
  await renderList([blog]);
  await screen.findByRole("heading", { name: String(blog.name) });
  return user();
}

describe("loading the blog list", () => {
  it("requests the blog list on mount, with credentials", async () => {
    await renderList();
    const fetchMock = fetch as unknown as ReturnType<typeof vi.fn>;
    expect(fetchMock.mock.calls[0]![1]).toMatchObject({ credentials: "include" });
  });

  it("selects the only blog instead of showing a one-button list", async () => {
    await openBlog();
    expect(screen.queryByRole("group", { name: "Blog wählen" })).toBeNull();
    expect(await screen.findByRole("heading", { name: "Hauptblog" })).toBeTruthy();
  });

  it("offers a picker once there is a choice", async () => {
    respond(/\/blogs\/haupt\/posts$/, { posts: [] }, 200, "GET");
    respond(/\/blog\/authors$/, { authors: [] }, 200, "GET");
    await renderList([BLOG, { id: 2, blog_key: "zweit", name: "Zweitblog" }]);
    const picker = await screen.findByRole("group", { name: "Blog wählen" });
    expect(within(picker).getByRole("button", { name: "Zweitblog" })).toBeTruthy();
  });

  it("sends the operator to Einstellungen when nothing is connected", async () => {
    // Adding a blog moved off this screen, so without the pointer the panel
    // would show an empty page with no way forward.
    await renderList([]);
    expect(await screen.findByText(/Noch kein Blog verbunden/)).toBeTruthy();
    expect(screen.getByRole("link", { name: /Einstellungen/ }).getAttribute("href")).toBe(
      "/einstellungen",
    );
  });

  it("offers no create form on the content screen", async () => {
    await renderList([]);
    await screen.findByText(/Noch kein Blog verbunden/);
    expect(screen.queryByRole("button", { name: /hinzufügen/i })).toBeNull();
  });

  it("reports the HTTP status rather than a calm empty list", async () => {
    // A 500 rendered as "no blogs" is indistinguishable from a fresh install,
    // which is the failure mode this whole codebase keeps re-learning.
    respond(/\/blogs$/, {}, 500, "GET");
    render(<BlogsList />);
    expect(await screen.findByRole("alert")).toHaveProperty(
      "textContent",
      expect.stringContaining("500"),
    );
  });

  it("tolerates a response with no blogs field", async () => {
    respond(/\/blogs$/, {}, 200, "GET");
    render(<BlogsList />);
    expect(await screen.findByText(/Noch kein Blog verbunden/)).toBeTruthy();
  });

  it("dims cached blogs and marks them busy while they revalidate", async () => {
    const clock = vi.spyOn(Date, "now").mockReturnValue(1_000);
    put("/blogs", { blogs: [BLOG, { id: 2, blog_key: "zweit", name: "Zweitblog" }] });
    clock.mockReturnValue(32_000);

    let release: (() => void) | undefined;
    handlers.unshift(async (url, init) => {
      if (pathOf(url) !== "/blogs" || (init?.method ?? "GET") !== "GET") return undefined;
      await new Promise<void>((resolve) => { release = resolve; });
      return { body: { blogs: [BLOG, { id: 2, blog_key: "zweit", name: "Zweitblog" }] } };
    });

    render(<BlogsList />);
    const picker = await screen.findByRole("group", { name: "Blog wählen" });
    const root = picker.closest(".blog-list") as HTMLElement;
    await waitFor(() => expect(root.classList.contains("tds-stale")).toBe(true));
    expect(root.getAttribute("aria-busy")).toBe("true");

    release?.();
    await waitFor(() => expect(root.classList.contains("tds-stale")).toBe(false));
    clock.mockRestore();
  });

  it("keeps cached blogs visible and warns when their refresh fails", async () => {
    const clock = vi.spyOn(Date, "now").mockReturnValue(1_000);
    put("/blogs", { blogs: [BLOG] });
    clock.mockReturnValue(32_000);
    respond(/\/blogs$/, {}, 500, "GET");

    render(<BlogsList />);
    expect(await screen.findByRole("heading", { name: "Hauptblog" })).toBeTruthy();
    expect(await screen.findByRole("alert")).toHaveProperty(
      "textContent",
      expect.stringContaining("veraltet"),
    );
    clock.mockRestore();
  });
});

describe("choosing a blog", () => {
  it("scopes the post request to the selected blog's key", async () => {
    respond(/\/blogs\/zweit\/posts$/, { posts: [] }, 200, "GET");
    respond(/\/blog\/authors$/, { authors: [] }, 200, "GET");
    await renderList([{ id: 2, blog_key: "zweit", name: "Zweitblog" }]);
    await waitFor(() => expect(calls.some((c) => pathOf(c.url) === "/blogs/zweit/posts")).toBe(true));
    expect(calls.some((c) => pathOf(c.url) === "/blogs/haupt/posts")).toBe(false);
  });

  it("loads that blog's posts and the author list", async () => {
    await openBlog();
    await waitFor(() => {
      expect(calls.some((c) => pathOf(c.url) === "/blogs/haupt/posts")).toBe(true);
      expect(calls.some((c) => pathOf(c.url) === "/blog/authors")).toBe(true);
    });
  });

  it("switches the article list when another blog is chosen", async () => {
    respond(/\/blogs\/haupt\/posts$/, { posts: [] }, 200, "GET");
    respond(/\/blogs\/zweit\/posts$/, { posts: [] }, 200, "GET");
    respond(/\/blog\/authors$/, { authors: [] }, 200, "GET");
    await renderList([BLOG, { id: 2, blog_key: "zweit", name: "Zweitblog" }]);
    const picker = await screen.findByRole("group", { name: "Blog wählen" });
    await user().click(within(picker).getByRole("button", { name: "Zweitblog" }));
    await waitFor(() => expect(calls.some((c) => pathOf(c.url) === "/blogs/zweit/posts")).toBe(true));
  });

  it("does not carry an open editor into another blog", async () => {
    respond(/\/blogs\/haupt\/posts$/, { posts: [] }, 200, "GET");
    respond(/\/blogs\/zweit\/posts$/, { posts: [] }, 200, "GET");
    respond(/\/blog\/authors$/, { authors: [] }, 200, "GET");
    await renderList([BLOG, { id: 2, blog_key: "zweit", name: "Zweitblog" }]);
    await user().click(await screen.findByRole("button", { name: "Neuer Beitrag" }));
    expect(screen.getByRole("heading", { name: "Neuer Beitrag" })).toBeTruthy();

    const picker = screen.getByRole("group", { name: "Blog wählen" });
    await user().click(within(picker).getByRole("button", { name: "Zweitblog" }));
    expect(await screen.findByRole("heading", { name: "Zweitblog" })).toBeTruthy();
    expect(screen.queryByRole("heading", { name: "Neuer Beitrag" })).toBeNull();
  });
});

describe("the post editor", () => {
  /** Open a blog and start a new post. */
  async function newPost() {
    // Register child requests before render: React may flush the selection
    // effect synchronously on a fast runner.
    respond(/\/blogs\/haupt\/posts$/, { posts: [] });
    respond(/\/blog\/authors$/, { authors: [{ id: 7, name: "Julian" }] });
    await renderList();
    const u = user();
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
    const u = await openBlog();
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
    // The single blog auto-selects during the first effect. All dependent
    // handlers must exist before render or the fast CI scheduler can answer
    // the posts request with the stub's empty default.
    respond(/\/blogs\/haupt\/posts$/, { posts: [POST] });
    respond(/\/blog\/authors$/, { authors: [] });
    respond(/\/posts\/hallo\?lang=de$/, { title: "Hallo", body: "Text", category: "news", draft: 0 });
    await renderList();
    const u = user();
    await u.click(await screen.findByRole("button", { name: /Hallo/ }));
    return u;
  }

  it("lists an existing post", async () => {
    respond(/\/blogs\/haupt\/posts$/, { posts: [POST] });
    respond(/\/blog\/authors$/, { authors: [] });
    await renderList();
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

  it("does not open a partial editor when loading the full post fails", async () => {
    respond(/\/blogs\/haupt\/posts$/, { posts: [POST] });
    respond(/\/blog\/authors$/, { authors: [] });
    respond(/\/posts\/hallo\?lang=de$/, {}, 500, "GET");
    await renderList();

    await user().click(await screen.findByRole("button", { name: /Hallo/ }));
    await waitFor(() =>
      expect(toasts.some((t) => t.variant === "danger" && t.message.includes("500"))).toBe(true),
    );
    expect(screen.queryByRole("heading", { name: "Beitrag bearbeiten" })).toBeNull();
  });
});

describe("the page cache", () => {
  const POST = { slug: "hallo", lang: "de", title: "Hallo", draft: 0, published_at: "2026-01-01" };
  const DRAFT = { slug: "entwurf", lang: "de", title: "Entwurf", draft: 1, published_at: null };

  it("rebuilds ONE article, not the whole corpus", async () => {
    // The case the page cache exists for: correcting one paragraph used to
    // cost a rebuild of every page in the blog.
    const u = await openBlog(BLOG, [POST]);
    await u.click(await screen.findByRole("button", { name: "Cache neu bauen" }));
    await waitFor(() => {
      const post = calls.find((c) => c.method === "POST");
      expect(pathOf(post!.url)).toBe("/blogs/haupt/cache/rebuild");
      expect(post!.body).toMatchObject({ slug: "hallo" });
    });
  });

  it("offers no rebuild for a draft — nothing of its is public", async () => {
    await openBlog(BLOG, [DRAFT]);
    await screen.findByRole("button", { name: /Entwurf/ });
    expect(screen.queryByRole("button", { name: "Cache neu bauen" })).toBeNull();
  });

  it("keeps a missing address in the flow rather than as a toast", async () => {
    // A vanishing message would leave the operator pressing a button that can
    // never work.
    const u = await openBlog(BLOG, [POST]);
    respond(/\/cache\/rebuild$/, {}, 422, "POST");
    await u.click(await screen.findByRole("button", { name: "Cache neu bauen" }));
    expect(await screen.findByRole("status")).toHaveProperty(
      "textContent",
      expect.stringContaining("keine Adresse"),
    );
  });

  it("keeps a missing cache token in the flow rather than claiming success", async () => {
    const u = await openBlog(BLOG, [POST]);
    respond(/\/cache\/rebuild$/, {}, 503, "POST");
    await u.click(await screen.findByRole("button", { name: "Cache neu bauen" }));
    expect(await screen.findByRole("status")).toHaveProperty(
      "textContent",
      expect.stringContaining("Token"),
    );
    expect(toasts.some((t) => t.variant === "success")).toBe(false);
  });

  it("reports the status when the rebuild fails outright", async () => {
    const u = await openBlog(BLOG, [POST]);
    respond(/\/cache\/rebuild$/, {}, 500, "POST");
    await u.click(await screen.findByRole("button", { name: "Cache neu bauen" }));
    await waitFor(() =>
      expect(toasts.some((t) => t.variant === "danger" && t.message.includes("500"))).toBe(true),
    );
  });
});

describe("what a save says afterwards", () => {
  /** Fill in the minimum a new post needs, save, and return the last toast. */
  async function saveNewPost(response: Record<string, unknown>, publish = true) {
    respond(/\/blogs\/haupt\/posts\/[a-z-]+$/, response, 200, "PUT");
    const u = await openBlog();
    await u.click(await screen.findByRole("button", { name: "Neuer Beitrag" }));
    await u.type(screen.getByPlaceholderText("mein-beitrag"), "hallo");
    await u.type(screen.getByPlaceholderText(/Titel/), "Hallo");
    await u.type(screen.getByPlaceholderText(/Text in Markdown/), "Text");
    if (publish) await u.click(screen.getByLabelText(/Veröffentlichen/));
    await u.click(screen.getByRole("button", { name: "Speichern" }));
    await waitFor(() => expect(toasts.length).toBeGreaterThan(0));
    return toasts[toasts.length - 1]!;
  }

  it("says the article's page rebuild was requested", async () => {
    const toast = await saveNewPost({ ok: true, cached: true });
    expect(toast.variant).toBe("success");
    expect(toast.message).toContain("angefragt");
  });

  it("does not promise a rebuild for a draft", async () => {
    // A draft never triggers one, so inferring it from `ok` would promise a
    // rebuild after every draft save.
    const toast = await saveNewPost({ ok: true, cached: false }, false);
    expect(toast.message).not.toContain("angefragt");
    expect(toast.message).toContain("nicht öffentlich");
  });

  it("does not claim a rebuild the API says did not happen", async () => {
    const toast = await saveNewPost({ ok: true, cached: false });
    expect(toast.message).not.toContain("angefragt");
  });
});

describe("translation controls", () => {
  it("reports the counts returned by a translation backfill", async () => {
    const u = await openBlog();
    respond(/\/translations\/backfill$/, { created: 3, skipped: 1 }, 200, "POST");
    await u.click(await screen.findByRole("button", { name: /Übersetzungen nachziehen/ }));
    await waitFor(() =>
      expect(
        toasts.some((t) => t.variant === "success" && /3 erstellt, 1 übersprungen/.test(t.message)),
      ).toBe(true),
    );
  });

  it("explains a 503 from a backfill as DeepL not being configured", async () => {
    const u = await openBlog();
    respond(/\/translations\/backfill$/, {}, 503, "POST");
    await u.click(await screen.findByRole("button", { name: /Übersetzungen nachziehen/ }));
    expect(await screen.findByText(/nicht konfiguriert/)).toBeTruthy();
  });

  it("points at the settings page, not at an env var nobody can edit", async () => {
    // A feature that can only be configured by editing a file on the host is,
    // on this Plesk host, a feature nobody has.
    const u = await openBlog();
    respond(/\/translations\/backfill$/, {}, 503, "POST");
    await u.click(await screen.findByRole("button", { name: /Übersetzungen nachziehen/ }));
    // Asserted on the STATUS message, not on the page text: the marginalia
    // above it also says "Einstellungen", so a loose match would pass with the
    // env-var wording still in the error.
    expect(await screen.findByRole("status")).toHaveProperty(
      "textContent",
      expect.stringContaining("Einstellungen"),
    );
  });

  it("survives a backfill success with an unparseable body", async () => {
    const u = await openBlog();
    handlers.unshift((url, init) =>
      /backfill/.test(url) && init?.method === "POST" ? { status: 200, body: undefined } : undefined,
    );
    await u.click(await screen.findByRole("button", { name: /Übersetzungen nachziehen/ }));
    await waitFor(() =>
      expect(
        toasts.some((t) => t.variant === "success" && /0 erstellt, 0 übersprungen/.test(t.message)),
      ).toBe(true),
    );
  });
});

describe("what moved to Einstellungen", () => {
  it("shows no rebuild repository field on the writing screen", async () => {
    // It sat directly above the article list: a GitHub repository name and a
    // deploy button, on the page somebody opens to write.
    await openBlog();
    expect(screen.queryByPlaceholderText("Tracht-Digital-Solutions/tds-blog-frontend")).toBeNull();
    expect(screen.queryByPlaceholderText("dev.yml")).toBeNull();
  });

  it("shows no page-cache address field on the writing screen", async () => {
    await openBlog();
    expect(screen.queryByPlaceholderText("https://blog.tracht-digital.de")).toBeNull();
  });

  it("offers no CI build button on the writing screen", async () => {
    // The expensive one is the wrong guess, so it is not one click from a typo
    // correction any more.
    await openBlog();
    expect(screen.queryByRole("button", { name: /Jetzt neu bauen/ })).toBeNull();
  });
});
