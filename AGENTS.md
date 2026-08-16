# AGENTS.md — tds-ext-blog-cms-pkg

Blog-CMS extension, ported from `tds-content-api`'s blog-post model. Read
`tds-frontend-contract-pkg` + `tds-core-frontend-api` AGENTS first.

## Model

- **Build-time content:** `blog_post` rows (one per blog × slug × language) are
  read by the static blogs at build time (published, non-draft). Never fetch this
  from the client at runtime (same rule as content-api).
- **1:n blogs:** the `blog` registry scopes posts. `blog_post.blog_id` FK →
  `blog` (CASCADE). Unique `(blog_id, slug, lang)`.
- **Auth via the core `UserContext`** — `blog:read`/`blog:write` (admins bypass).
  Posts are upserted (PUT, `ON DUPLICATE KEY`). A non-draft save stamps
  `published_at` when none is set.
- **`set:html` on the body stays unsanitised only while admin-authored + baked at
  build** — add `isomorphic-dompurify` the day a non-admin can write a body or a
  client preview ships (carried from content-api/tds-admin).

## Gotchas

- **Call the API with `apiFetch` from `@tracht-digital-solutions/tds-shared/api`,
  never a relative `fetch`.** Every island used to define its own
  `const api = (path, init) => fetch(path, { credentials: "include", ...init })`
  with a RELATIVE path. In a product that resolves against the product's own
  static host (`management.`/`app.tracht-digital.de`), not the API — and the
  static host answers unknown paths with its SPA fallback, i.e. **200 + HTML**.
  So `res.ok` is `true`, `res.json()` throws, and the usual
  `.catch(() => setRows([]))` renders a calm, permanent empty state with no
  error and no console warning. `apiFetch` resolves the base from
  `<meta name="tds-api-base">` (written by the frontend host) and routes 401s
  through the host's confirm-against-`/me` backstop, which extension calls
  previously skipped entirely.
  The island tests match on the request PATH (`pathOf()`), which a relative
  fetch satisfies just as well — so one assertion per suite pins the **absolute
  host**. That is the line that fails if this ever regresses.


- **Public read surface (UNAUTHENTICATED).** Alongside the admin (`blog:read`/
  `blog:write`) routes, this module serves the successor to tds-content-api's open
  public read that the public blog + landingpage SSG builds fetch: `GET
  /content/blog` (published list, `lang`/`limit`/`cursor`), `/content/blog/popular`
  (newest — no view counter), `/content/blog/{slug}`, `/content/topics` (null) and
  `/content/snippets` (`[]`). Only `draft=0, published_at IS NOT NULL` rows leak;
  the response is the camelCase `BlogPost` shape tds-shared defines (markdown body →
  the frontend renders it). Maps the single public site to the **default blog**
  (`defaultBlog()`). These routes **degrade to an empty payload on any DB error**
  (build-fetch fail-safe) — keep them read-only and ungated.
- Migration class names are **module-prefixed** (`BlogCms*`) AND the numeric
  **version prefixes are globally unique** (this module owns the `20260728*`
  band) — every composed module's migrations share one `phinxlog`, so a reused
  class name OR version collides. Keep new migrations in this band.
- **`BlogCmsSeedPosts` (`20260728000007`) ships the six launch articles, DE + EN.**
  A fresh installation has **no `blog` row at all** — nothing creates one except an
  operator clicking through `/blogs`, and until then `defaultBlog()` is null and
  `/content/blog` answers `{"posts": []}` forever. So the seed creates the default
  blog when none exists (reusing an existing one rather than adding a second the
  public read would never see), seeds the `blog_author` byline, and writes the
  posts with **`draft = 0` plus a non-null `published_at`** — miss either and the
  rows exist but are invisible, with no error anywhere.
  Three properties to preserve when editing it:
  - **The English rows carry `machine_translated = 0`.** They are hand-written.
    Flagged as machine-translated, `Service\TranslationSync` overwrites every one
    of them with DeepL output the next time somebody saves the German article,
    and the frontend labels hand-written prose as machine-translated.
  - **Idempotent by `(blog_id, slug, lang)`**, and `down()` deletes only rows
    still carrying the seeded title *and* body verbatim — an operator's edits
    survive a rollback. `down()` deliberately keeps the blog and author rows.
  - **The slugs are mirrored in `tds-landingpage-frontend`** (`lib/content.ts`'s
    `topicFallback` and tds-shared's `blog.posts` fallback teasers). Renaming a
    slug here without changing those publishes links to 404s, and nothing in
    either build checks it.
- Routes are closures resolving `UserContext`/`BlogRepository` from the container
  at request time (rebound per request by the core AuthMiddleware).
- DB-backed tests skip without `TDS_TEST_DB_DSN`; the committed test covers
  routes + RBAC + payload validation without a DB.
- **`renderMarkdown` lives in tds-shared now** (`@tracht-digital-solutions/tds-shared/markdown`,
  `>=0.20.2`), not in `islands/BlogsList.tsx`. The customer wiki renders handbook
  articles with the same function, and an XSS boundary must not exist twice. Its
  test suite moved with it (`src/markdown/markdown.test.ts` over there) — do not
  re-inline a local copy here.
  It is escape-FIRST (every text run is HTML-escaped before any md transform),
  which is what lets the panel skip dompurify — see the root `CLAUDE.md`. The
  hard-won detail, if you ever touch it: **`safeHref` must not escape again**,
  because `inlineMd` already receives escaped text, and a second pass
  double-encoded ampersands so every link with a query string (`?a=1&b=2`)
  resolved to the wrong URL. Fixed in 0.1.24, carried over in the move.
- **Outcomes are toasts; a CONFIGURATION problem is not an outcome.** Saves,
  deletes, the rebuild trigger and the translation backfill report through
  `toast` (tds-shared `>=0.16.0`) — but a 503 "DeepL not configured" / 503 "no
  rebuild token" / 422 "no repository" stays in the in-flow banner, because it
  names something an operator has to go and fix rather than something that just
  happened. The "…" progress lines also stay in-flow and are cleared when the
  outcome arrives. Never mount a `ToastHost` here — the frontend host owns the
  only one.

## Tests

`npm run test:run` (vitest; jsdom per-file via a `@vitest-environment` docblock).

- The escape-first renderer's own suite now lives in **tds-shared**
  (`src/markdown/markdown.test.ts`), because the function does: script/img/iframe
  payloads stay inert text, the href allow-list refuses
  `javascript:`/`data:`/`vbscript:`, and no tag outside the emitted allow-list
  ever reaches the DOM. If one of those fails, admin markdown can execute in the
  frontend — of two products now, not one.
- `islands/BlogsList.test.tsx` — blog + post CRUD through the real UI against a
  URL/method-matching fetch stub, so each test asserts the exact request the PHP
  module receives (payload trimming, the `?lang=` on delete, the 503/422 status
  copy).
- `islands/BlogSettings.test.tsx` — the masked-secret contract: a secret never
  round-trips to the DOM, and a **blank** secret on save means *keep*, so an
  admin toggling auto-translate cannot wipe the DeepL key.
- `src/index.test.ts` + `tests/packaging.test.ts` — the manifest as a product
  build sees it: every referenced permission is declared, every specifier
  resolves to a real file that is both covered by `exports` and inside the
  published `files` list. These failures otherwise surface in someone else's
  product build.

Verified by mutation: 23 deliberate breakages introduced, 23 caught.

## Checkpoint status

- **CP1:** `blog` + `blog_post` schema, `Domain\BlogRepository`, blog + post CRUD
  (`/blogs`, `/blog/summary`) with RBAC, the posts widget + blogs/posts list UI.
- **CP2:** the per-post **markdown editor UI** (`PostEditor` in `islands/BlogsList.tsx`)
  — "Neuer Beitrag" / open a post (slug + lang → GET), edit title/category/excerpt/
  cover-hint + a markdown body textarea, toggle draft↔publish, save via PUT, delete via
  DELETE. Slug + lang lock when editing an existing post (they're the row identity).
- **CP3:** save-triggered **static-blog rebuild**. `Service\RebuildTrigger` (plain
  ext-curl, best-effort, never throws) fires a GitHub `workflow_dispatch` after a
  **published** post is saved (drafts don't rebuild) or a post is deleted. Per-blog
  target on `blog` (`rebuild_repo` "owner/name" + `rebuild_workflow`, default
  `dev.yml`), edited via `PUT /blogs/{blog}/rebuild-config`; the shared PAT is
  `BLOG_REBUILD_TOKEN` (one PAT dispatches every blog repo; unset ⇒ no-op).
  `POST /blogs/{blog}/rebuild` is a manual "Jetzt neu bauen" (503 no token / 422 no
  repo). Sends `ref` only. UI: a Rebuild-Konfiguration block under the post list.
- **CP4:** **DeepL auto-translation** (save-time sync, ported from tds-content-api).
  `blog_post.machine_translated` flags auto-generated rows. On a **published** post
  save, `Service\TranslationSync` translates title/excerpt/category (plain) + body
  (markdown, code shielded) into the counterpart language and upserts it
  (`machine_translated=1`) — but only when that counterpart is absent or itself
  machine-made; a manually authored counterpart is never touched, and a manual save
  clears the row's own flag. Delete cascades onto a machine counterpart. `Service\
  DeeplTranslator` is a curl port (no Guzzle; `:fx` key ⇒ free endpoint; `en`→`EN-GB`).
  Config: `BLOG_DEEPL_API_KEY` (+ `BLOG_AUTO_TRANSLATE=0` to opt out); unset ⇒ no-op.
  `POST /blogs/{blog}/translations/backfill` (blog:write, 503 when inactive) catches up
  pre-existing posts. UI: an "Auto-Übersetzung" badge on machine rows + a backfill button.
  Writes go through the repo (never the route) so the sync can't ping-pong; drafts skip.
- **CP5:** **author bylines**. A self-contained `blog_author` registry (name/bio/
  avatar_url), independent of frontend users, with a nullable `blog_post.author_id` FK
  (`ON DELETE SET NULL` — removing an author detaches its posts, never cascades).
  Routes: `GET`/`POST /blog/authors` + `DELETE /blog/authors/{id}` (read/write RBAC).
  The post upsert takes an optional `author_id` (an unknown id is dropped, not
  rejected); `getPost` returns a nested `author` object for the public byline and
  `posts` includes `author_name`. TranslationSync carries the same `author_id` onto
  the machine-translated counterpart (one byline across languages). UI: an author
  dropdown in the editor + an "Autoren" manager (add/remove) under the post list.
- **CP6:** **SEO fields** — `blog_post.meta_description` (≤300) + `tags` (≤200,
  comma-separated keyword tokens), both nullable (a post without them falls back to
  excerpt/category on the public page). Surfaced in the editor, returned by
  `getPost` for the static blog to bake `<meta name=description>`/keywords.
  TranslationSync translates the meta description onto the counterpart (batched with
  the core fields) but keeps `tags` identical across languages (stable keyword
  tokens). Migration `AddBlogCmsSeo`.
- **CP7:** a **markdown preview pane** in the editor (Vorschau/Bearbeiten toggle).
  Uses a tiny **escape-first** renderer (`renderMarkdown`, since 0.1.31 from
  `@tracht-digital-solutions/tds-shared/markdown`):
  every text run is HTML-escaped *before* any markdown transform, so raw HTML /
  `<script>` in the body become inert text and link hrefs are allowlisted
  (http/https/mailto/relative only) — **safe by construction, no marked/dompurify
  dependency** (this is why the `set:html` sanitiser note doesn't apply to the
  preview). Covers fenced/inline code, headings, bold, italic, links, unordered
  lists, paragraphs; the public blog still uses the full build-time pipeline.
- **CP8:** **runtime settings store adoption.** The DeepL key + auto-translate flag
  + rebuild token are now read **DB-first with env fallback** via the core's
  `SettingsStore` (contract interface, resolved from the container; null in isolated
  tests ⇒ env-only). Namespace `blog-cms`, keys `deepl_api_key`/`rebuild_token`
  (secret, AES-GCM-encrypted by the core) + `auto_translate` (flag). The settings
  slot (`islands/Settings.astro` → `BlogSettings` island) reads/writes the core admin
  API `/admin/settings/blog-cms` (masked: `configured`+`last4`; blank secret = keep).
  Env vars (`BLOG_DEEPL_API_KEY`/`DEEPL_API_KEY`, `BLOG_AUTO_TRANSLATE`,
  `BLOG_REBUILD_TOKEN`) remain the fallback, so existing deployments keep working.
- **CP9:** **authors tied to frontend users.** `blog_author.user_id` (nullable,
  unsigned, unique — NOT a DB FK; app_user lives in another service, same rule as
  the ticket refs) links a byline to a tds-auth-api user; the row stays a SNAPSHOT
  (name/bio/avatar) so the byline survives a user removal. `POST /blog/authors` now
  takes an optional `user_id` → `upsertAuthorFromUser` (one snapshot per user);
  absent ⇒ a free-form/guest author. The AuthorManager fetches `/auth/admin/users`
  (relative, same-origin gateway convention), filters to blog authors
  (`isBlogAuthor || isAdmin`), and imports them as authors (a "Frontend-Nutzer" chip
  marks linked ones); free-form add stays for guests. Falls back gracefully when
  `/auth/admin/users` is unreachable.
- **TODO (next):** the website-cms equivalent has no author concept (blocks, not
  posts); a markdown preview is done. Larger: per-section structured CMS forms.

## After a change

Bump `version` in `package.json` + `composer.json` (lockstep), update docs,
commit together.

## Mobile layout

This package ships **no CSS**, so every layout decision is a shared class or a
Tailwind utility, and neither is checked by anything at runtime. Two rules:

- **A row of more than two things — or any row holding a full-width field —
  goes on `.tds-row`, `.tds-list__row` or `.tds-toolbar`.** All three wrap.
  A hand-rolled `flex` does not, and on a 375px screen the overflow is not
  even visible: `body { overflow-x: hidden }` clips it, so the content simply
  is not there.
- **A `<table>` needs `tds-table` and nothing else.** The primitive turns
  itself into a horizontal scroller below 40rem; an extra `overflow-x`
  wrapper or an inline style is redundant. A table with no focusable cell
  also needs `tabindex="0"` + `role="region"` + a label, or its scrollport
  cannot be reached by keyboard.

`npm run lint:primitives` enforces the class part of this (including a
`<table>` without `tds-table` and a flex/grid table cell, which silently
drops the cell out of the column algorithm). It is a **regex scan**, so a tag
name written inside a comment counts as markup — name elements in prose.

## API-Referenz (`php/docs/api.php`)

This module implements the contract's optional `ApiDocSource`: `php/docs/api.php`
returns one entry per route (summary, params, responses, required permission),
and the admin frontend's API reference joins it onto the introspected Slim routes
by `"<METHOD> <pattern>"`. Two things to know before editing a route:

- **`pattern` must be the Slim pattern verbatim**, inline regex included
  (`/blogs/{blog:[a-z0-9-]+}/posts/{slug:[a-z0-9-]+}`). A prettified path silently produces an orphan doc *and* an
  undocumented route rather than an error.
- **`php/tests/BlogCmsApiDocsTest.php` asserts both directions** — the documented
  set and the registered set must be the same set, every path placeholder must
  be described, and a named permission must exist in `permissions()`. Adding or
  renaming a route without touching `docs/api.php` fails there. That is the
  point: prose next to code rots, and a reference full of confident, wrong
  detail is worse than the bare route list it replaced.
