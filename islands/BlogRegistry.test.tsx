// @vitest-environment jsdom
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { primeRuntimeConfig } from "@tracht-digital-solutions/tds-shared/api";
import { resetCache } from "@tracht-digital-solutions/tds-shared/data";
import { cleanup, render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import BlogRegistry from "./BlogRegistry";
import { TOAST_EVENT } from "@tracht-digital-solutions/tds-shared/toast";

/**
 * The managed-blogs registry, in Einstellungen.
 *
 * This is where a blog is ADDED — it used to sit on the content screen, above
 * the article list, next to a GitHub repository field and a deploy button. The
 * tests below are about the two things that cost something: the key can never
 * be corrected later, and the two rebuild buttons do completely different
 * things while sounding alike.
 */

type Hit = { status?: number; body?: unknown };
let handlers: Array<(url: string, init?: RequestInit) => Hit | undefined> = [];
let calls: Array<{ url: string; method: string; body: unknown }> = [];

const pathOf = (url: string) => String(url).replace(/^https?:\/\/[^/]+/i, "");

function respond(match: RegExp, body: unknown, status = 200, method?: string) {
  handlers.unshift((url, init) => {
    if (!match.test(pathOf(url))) return undefined;
    if (method && (init?.method ?? "GET") !== method) return undefined;
    return { status, body };
  });
}

let toasts: Array<{ variant: string; message: string }> = [];
const collectToast = (e: Event) => {
  toasts.push((e as CustomEvent<{ variant: string; message: string }>).detail);
};

beforeEach(() => {
  resetCache();
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
        const hit = h(url, init);
        if (hit) {
          const status = hit.status ?? 200;
          return { ok: status >= 200 && status < 300, status, json: async () => hit.body ?? {} } as Response;
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

const BLOG = {
  id: 1,
  blog_key: "haupt",
  name: "Hauptblog",
  rebuild_repo: "Tracht-Digital-Solutions/tds-blog-frontend",
  rebuild_workflow: "dev.yml",
  cache_url: "https://blog.tracht-digital.de",
  updated_at: "2026-01-01",
};

async function renderRegistry(blogs: unknown[] = [BLOG]) {
  // Method-scoped: `respond` puts the newest matcher first, so an unscoped GET
  // handler registered here would also answer a POST a test set up earlier.
  respond(/\/blogs$/, { blogs }, 200, "GET");
  render(<BlogRegistry />);
  await waitFor(() => expect(calls.some((c) => pathOf(c.url) === "/blogs")).toBe(true));
  return user();
}

const posts = () => calls.filter((c) => c.method === "POST");
const puts = () => calls.filter((c) => c.method === "PUT");

describe("adding a blog", () => {
  it("posts a valid kebab key and name", async () => {
    const u = await renderRegistry([]);
    await u.type(screen.getByLabelText("Schlüssel"), "shop");
    await u.type(screen.getByLabelText("Name"), "Shop");
    await u.click(screen.getByRole("button", { name: "Blog hinzufügen" }));
    await waitFor(() => expect(posts()).toHaveLength(1));
    expect(pathOf(posts()[0]!.url)).toBe("/blogs");
    expect(posts()[0]!.body).toMatchObject({ blog_key: "shop", name: "Shop" });
  });

  it("refuses an invalid key before it reaches the API", async () => {
    // The key is the join between the content and the public site and cannot
    // be changed afterwards, so a typo is a site nobody can edit.
    const u = await renderRegistry([]);
    await u.type(screen.getByLabelText("Schlüssel"), "Shop Site");
    await u.type(screen.getByLabelText("Name"), "Shop");
    await u.click(screen.getByRole("button", { name: "Blog hinzufügen" }));
    expect(await screen.findByRole("alert")).toBeTruthy();
    expect(posts()).toHaveLength(0);
  });

  it("refuses a blank name", async () => {
    const u = await renderRegistry([]);
    await u.type(screen.getByLabelText("Schlüssel"), "shop");
    await u.click(screen.getByRole("button", { name: "Blog hinzufügen" }));
    expect(await screen.findByRole("alert")).toBeTruthy();
    expect(posts()).toHaveLength(0);
  });

  it("reports the HTTP status when the create fails", async () => {
    // 409 (already exists) and 403 (not allowed) need very different fixes.
    respond(/\/blogs$/, {}, 409, "POST");
    const u = await renderRegistry([]);
    await u.type(screen.getByLabelText("Schlüssel"), "haupt");
    await u.type(screen.getByLabelText("Name"), "Nochmal");
    await u.click(screen.getByRole("button", { name: "Blog hinzufügen" }));
    await waitFor(() => expect(toasts.length).toBeGreaterThan(0));
    expect(toasts[toasts.length - 1]!.message).toContain("409");
  });

  it("clears the form after a successful create", async () => {
    const u = await renderRegistry([]);
    await u.type(screen.getByLabelText("Schlüssel"), "shop");
    await u.type(screen.getByLabelText("Name"), "Shop");
    await u.click(screen.getByRole("button", { name: "Blog hinzufügen" }));
    await waitFor(() => expect(posts()).toHaveLength(1));
    await waitFor(() => expect((screen.getByLabelText("Schlüssel") as HTMLInputElement).value).toBe(""));
  });
});

describe("per-blog configuration", () => {
  it("saves the cache address and the rebuild target together", async () => {
    const u = await renderRegistry();
    const url = await screen.findByLabelText("Adresse des öffentlichen Blogs");
    await u.clear(url);
    await u.type(url, "https://neu.example");
    await u.click(screen.getByRole("button", { name: "Konfiguration speichern" }));
    await waitFor(() => expect(puts()).toHaveLength(1));
    expect(pathOf(puts()[0]!.url)).toBe("/blogs/haupt/rebuild-config");
    expect(puts()[0]!.body).toMatchObject({
      cache_url: "https://neu.example",
      rebuild_repo: "Tracht-Digital-Solutions/tds-blog-frontend",
      rebuild_workflow: "dev.yml",
    });
  });

  it("keeps an invalid cache origin in the flow", async () => {
    respond(/\/blogs\/haupt\/rebuild-config$/, {}, 422, "PUT");
    const u = await renderRegistry();
    const url = await screen.findByLabelText("Adresse des öffentlichen Blogs");
    await u.clear(url);
    await u.type(url, "https://user:pass@blog.example/path");
    await u.click(screen.getByRole("button", { name: "Konfiguration speichern" }));

    expect(await screen.findByRole("alert")).toHaveProperty(
      "textContent",
      expect.stringContaining("reine http(s)-Origin"),
    );
  });

  it("keeps the two rebuild buttons on separate routes", async () => {
    // They sound alike and are not: one dispatches a CI build that ships code,
    // the other re-renders pages from content already stored. Confusing them
    // costs minutes and a deploy.
    const u = await renderRegistry();
    await u.click(await screen.findByRole("button", { name: "Seiten-Cache neu bauen" }));
    await waitFor(() => expect(posts()).toHaveLength(1));
    expect(pathOf(posts()[0]!.url)).toBe("/blogs/haupt/cache/rebuild");

    await u.click(screen.getByRole("button", { name: "Jetzt neu bauen (CI)" }));
    await waitFor(() => expect(posts()).toHaveLength(2));
    expect(pathOf(posts()[1]!.url)).toBe("/blogs/haupt/rebuild");
  });

  it("asks the cache to rebuild everything, not one article", async () => {
    // This button is the catch-up for when a save's targeted rebuild did not
    // land, so it must not be targeted itself.
    const u = await renderRegistry();
    await u.click(await screen.findByRole("button", { name: "Seiten-Cache neu bauen" }));
    await waitFor(() => expect(posts()).toHaveLength(1));
    // The blog side sends an EMPTY body for "everything"; the per-article
    // form is what carries a slug.
    expect(posts()[0]!.body).toEqual({});
  });

  it("keeps a missing configuration in the flow rather than as a toast", async () => {
    // A vanishing message would leave the operator pressing a button that can
    // never work.
    respond(/\/blogs\/haupt\/rebuild$/, {}, 422, "POST");
    const u = await renderRegistry();
    await u.click(await screen.findByRole("button", { name: "Jetzt neu bauen (CI)" }));
    expect(await screen.findByRole("status")).toHaveProperty(
      "textContent",
      expect.stringContaining("kein Repository"),
    );
  });

  it("reports the status when the cache rebuild fails outright", async () => {
    respond(/\/blogs\/haupt\/cache\/rebuild$/, {}, 500, "POST");
    const u = await renderRegistry();
    await u.click(await screen.findByRole("button", { name: "Seiten-Cache neu bauen" }));
    await waitFor(() => expect(toasts.length).toBeGreaterThan(0));
    expect(toasts[toasts.length - 1]!.message).toContain("500");
  });

  it("keeps a missing cache token in the flow rather than claiming success", async () => {
    respond(/\/blogs\/haupt\/cache\/rebuild$/, {}, 503, "POST");
    const u = await renderRegistry();
    await u.click(await screen.findByRole("button", { name: "Seiten-Cache neu bauen" }));
    expect(await screen.findByRole("status")).toHaveProperty(
      "textContent",
      expect.stringContaining("Token"),
    );
    expect(toasts.some((t) => t.variant === "success")).toBe(false);
  });
});

describe("the list itself", () => {
  it("reports the HTTP status instead of an empty registry", async () => {
    respond(/\/blogs$/, {}, 403, "GET");
    render(<BlogRegistry />);
    expect(await screen.findByRole("alert")).toHaveProperty(
      "textContent",
      expect.stringContaining("403"),
    );
  });

  it("says plainly when nothing is connected", async () => {
    await renderRegistry([]);
    expect(await screen.findByText(/Noch kein Blog verbunden/)).toBeTruthy();
  });
});
