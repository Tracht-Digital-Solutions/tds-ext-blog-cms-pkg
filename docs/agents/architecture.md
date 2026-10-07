# Architecture

## Model

- **1:n blogs.** The `blog` registry scopes posts. `blog_post.blog_id` → `blog` (CASCADE).
  Unique `(blog_id, slug, lang)`, one row per blog × slug × language.
- Auth is the core `UserContext`: `blog:read` / `blog:write` (admins bypass). Routes resolve
  `UserContext` and `BlogRepository` from the container at request time.
- Posts are **upserted** (`PUT`, `ON DUPLICATE KEY`). A non-draft save stamps `published_at`
  when none is set. Slug and language lock when editing an existing post; they are the row
  identity.
- SEO fields: `meta_description` (≤ 300) and `tags` (≤ 200, comma-separated tokens), both
  nullable. They are returned **only on the full-post read**, not in the list payload.

## Admin routes

| Area | Routes |
|---|---|
| Blogs | `GET` / `POST /blogs`, `GET /blog/summary` |
| Connection | `GET` / `DELETE /blogs/{blog}/connection`, `POST /blogs/{blog}/connection/pairing` |
| Posts | `GET /blogs/{blog}/posts`, `GET` / `PUT` / `DELETE /blogs/{blog}/posts/{slug}` (`DELETE` takes `?lang=`) |
| Authors | `GET` / `POST /blog/authors`, `DELETE /blog/authors/{id}` |
| Translation | `POST /blogs/{blog}/translations/backfill` (503 when DeepL is inactive) |
| Cache | `POST /blogs/{blog}/cache/rebuild` |

Public routes: [public-api.md](public-api.md).

## Surfaces

- **Registration and writing are separate.** Blogs are created and connected only under
  *Einstellungen → Blog-CMS* (`BlogRegistry`). `/blog` is the daily content surface: it
  auto-selects the only blog or shows a picker, then selects and edits an article. Never move
  connection or cache-origin fields back beside the article list.
- **Panel reads are stale-while-revalidate** via tds-shared's `./data` entry point (≥ 0.33.0).
  Cached rows paint immediately on a return navigation, wear `tds-stale` + `aria-busy` while
  refreshing, and stay visible with an explicit warning if the refresh fails. A failed refresh
  never becomes a calm empty list.

## Editor

- `PostEditor` in `islands/BlogsList.tsx`: title, category, excerpt, cover hint, markdown body,
  draft ↔ publish, SEO fields, author.
- Preview uses **`renderMarkdown` from `@tracht-digital-solutions/tds-shared/markdown`**
  (≥ 0.20.2). The customer wiki renders with the same function, and an XSS boundary must not
  exist twice; never re-inline a copy. It is **escape-first**: every text run is HTML-escaped
  before any markdown transform, and hrefs are allow-listed (http, https, mailto, relative).
  `safeHref` must not escape again, or `?a=1&b=2` links break.
- `set:html` on the public body stays unsanitised only while bodies are admin-authored. Add
  `isomorphic-dompurify` the day a non-admin can write a body.

## Authors

- `blog_author` (name, bio, avatar_url) is a self-contained registry. `blog_post.author_id` is a
  nullable FK with `ON DELETE SET NULL`: removing an author detaches posts, never deletes them.
- `blog_author.user_id` (nullable, unsigned, unique, **no** DB FK; `app_user` lives in
  `tds-auth-api`) links a byline to a user. The row stays a **snapshot**, so the byline survives
  a user removal. `POST /blog/authors` with `user_id` upserts one snapshot per user; without it,
  it's a guest author.
- The author manager imports users from `/auth/admin/users` filtered to
  `isBlogAuthor || isAdmin`, and degrades gracefully when that is unreachable.
- An unknown `author_id` on a post save is dropped, not rejected. `getPost` returns a nested
  `author`; `posts` includes `author_name`.

## DeepL auto-translation

- On a **published** save, `Service\TranslationSync` translates title, excerpt, category,
  meta description and the markdown body (code shielded) into the counterpart language and
  upserts it with `machine_translated = 1`.
- Only when the counterpart is absent or itself machine-made. A manually written counterpart is
  never touched, and a manual save clears the row's own flag.
- `tags` and `author_id` carry over unchanged. Deleting a post cascades to a machine
  counterpart. Drafts skip.
- Writes go through the repository, never the route, so sync can't ping-pong.
- `Service\DeeplTranslator` is plain curl (`:fx` key ⇒ free endpoint; `en` → `EN-GB`).
- UI: an "Auto-Übersetzung" badge on machine rows and a backfill button.

## Settings (core `SettingsStore`, namespace `blog-cms`)

| Key | Secret | Env fallback |
|---|---|---|
| `deepl_api_key` | yes | `BLOG_DEEPL_API_KEY`, then `DEEPL_API_KEY` |
| `auto_translate` | no | `BLOG_AUTO_TRANSLATE` (`0` opts out) |
| `cache_token` | yes | `BLOG_CACHE_TOKEN` |

The settings slot (`islands/Settings.astro` → `BlogSettings`) uses the core
`/admin/settings/blog-cms`: masked on read (`configured` + `last4`), **blank secret = keep**, so
toggling auto-translate can't wipe the DeepL key. Runtime GitHub rebuild settings were removed
by `BlogCmsRemoveGithubRuntime`; releases are CI-owned.

## Outcomes

Saves, deletes, cache refreshes and the backfill report via toast. A configuration problem
(503 "DeepL not configured", 503 "no cache token") stays in the in-flow banner, because an
operator has to fix it. "…" progress lines stay in-flow and clear when the outcome arrives.

## Motion (peer ≥ 0.38.2)

The blog picker has a `TabIndicator` and cross-fades between blogs; list and editor swap with
`Presence`; post and author rows are `AnimatedList`s; in-flow status lines open with
`Collapse`. List and editor are JSX variables, never inner components. Exiting content stays
in the DOM (`aria-hidden` + `inert`), so tests `findBy…` what arrives and `waitFor` what leaves.
