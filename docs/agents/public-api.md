# Public read API, site keys and page cache

## Public reads

The successor to `tds-content-api`'s open read. The public blog and landing page fetch these
server-side when rendering; never from a visitor's browser.

| Route | Returns |
|---|---|
| `GET /content/blog` | Published list; `lang`, `limit`, `cursor` |
| `GET /content/blog/popular` | Newest posts (there is no view counter) |
| `GET /content/blog/{slug:[a-z0-9-]+}` | One post |
| `GET /content/topics` | `null` |
| `GET /content/snippets` | `[]` |

- Only rows with `draft = 0` **and** `published_at IS NOT NULL` are returned.
- The response is the camelCase `BlogPost` shape tds-shared defines; the body is markdown.
- The public site maps to the **default blog** (`defaultBlog()`). Without any `blog` row it is
  null and `/content/blog` answers `{"posts": []}`.
- These routes **degrade to an empty payload on any DB error**. Keep them read-only and free of
  permission checks.

## Site keys (`SiteKeyProtected`)

`siteKeyRoutes()` declares `/content/blog`, `/content/topics`, `/content/snippets`.

- **Prefixes, not patterns.** `/content/blog` also covers `/content/blog/{slug}` and
  `/content/blog/popular`.
- **Never widen to `/content`.** That would gate website-cms's `/content/landing` and
  `/content/legal`, another module's surface.
- **Never list a route a visitor's browser calls.** A browser has no key, so `enforce` would
  become an outage on the public site.
- `php/tests/BlogCmsApiDocsTest.php` asserts every declared prefix covers a mounted route and
  none reaches an `/admin` route. An orphaned prefix leaves an **unprotected** route that looks
  deliberate.

## Page cache

- Each `blog` row has a `cache_url`; the shared token is `blog-cms/cache_token`
  (`BLOG_CACHE_TOKEN` fallback). A paired site connection (`/blogs/{blog}/connection`) is used
  via `ConnectedSiteCache` when present, otherwise the core `SiteCache`.
- `BlogRepository::blogs()`, `findBlog()` and the id lookup must **SELECT `cache_url`**.
  Omitting it makes every save and manual refresh a silent no-op.
- `Support\CacheOrigin` accepts only a pure http(s) origin (no userinfo, path, query or
  fragment), because the cache token is sent there.
- The module sends content **events** (`post` + slug + optional language), never URLs. A
  published save sends one event for the article, covering both language trees when it also
  rewrote the machine translation.
- `fireCache()` returns whether a request was dispatched; the save response exposes it as
  `cached`, and the manual route answers 503 instead of 202 when token or origin is missing.
- The transport is best-effort and can't prove the site finished rendering. UI copy says the
  refresh was **requested**, not completed.
