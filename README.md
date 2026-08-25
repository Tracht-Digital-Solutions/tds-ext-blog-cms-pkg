# tds-ext-blog-cms-pkg

Blog-CMS extension for the TDS panel platform. One panel can manage several
blogs; every post is identified by **blog × slug × language** and can carry
markdown, draft/publication state, SEO fields, a byline and a machine-generated
counterpart language.

## Panel workflow

- **Einstellungen → Blog-CMS** is the only place a blog is registered. It owns
  the immutable blog key, display name, CI repository/workflow and public
  page-cache origin. DeepL, CI and page-cache tokens are stored there as masked
  runtime secrets.
- **Blog-CMS → `/blog`** is the writing surface. It auto-selects the sole blog or
  offers a blog picker, then lists articles and opens the selected article in the
  markdown editor. Blog registration and deployment fields intentionally do not
  appear here.
- Blog, article and author reads use the shared in-memory stale-while-revalidate
  cache. Returning to the screen paints the last rows immediately; while they
  refresh they are dimmed/pulsing (`tds-stale`) and marked `aria-busy`. A failed
  refresh keeps the old rows visible and labels them as possibly stale.

## Page cache versus CI rebuild

These are separate operations:

- A **page-cache rebuild** re-renders pages from content already stored. Saving
  a published post emits a content event for that slug and language; if the same
  save rewrites its machine translation, both language trees are requested. A
  per-article catch-up button uses the same targeted route.
- A **CI rebuild** dispatches the configured GitHub workflow and ships code or
  design changes. It is slower and belongs in settings.

The page cache requires both `blog.cache_url` and the secret
`blog-cms/cache_token` (`BLOG_CACHE_TOKEN` remains the environment fallback).
`cache_url` is accepted only as a pure HTTP(S) origin; userinfo, paths, queries
and fragments are rejected before the token can be sent.
The API returns `cached: true` only when a request was actually dispatched. The
transport is best-effort, so the panel says that a rebuild was *requested*; it
does not claim that the public site has already finished rendering.

## API surface

- Registry and editor: `GET`/`POST /blogs`, `GET
  /blogs/{blog}/posts`, `GET`/`PUT`/`DELETE
  /blogs/{blog}/posts/{slug}`.
- Authors and translation: `GET`/`POST /blog/authors`, `DELETE
  /blog/authors/{id}`, `POST /blogs/{blog}/translations/backfill`.
- Rebuild configuration/actions: `PUT /blogs/{blog}/rebuild-config`, `POST
  /blogs/{blog}/cache/rebuild`, `POST /blogs/{blog}/rebuild`.
- Dashboard: `GET /blog/summary`.
- Public read (unauthenticated): `GET /content/blog`, `/content/blog/popular`,
  `/content/blog/{slug}`, `/content/topics` and `/content/snippets`. Only
  published rows are returned; database failures degrade to the documented
  empty/fallback shapes for public-site fetches.

Admin routes use the core `UserContext` permissions `blog:read` and
`blog:write`; public read routes are ungated. Data comes from the core `PDO`.

## Runtime requirements

- `@tracht-digital-solutions/tds-shared >=0.33.0` for the `./data` SWR export.
- `tracht-digital-solutions/tds-frontend-contract ^1.10.0` for `SiteCache` and
  `CacheEvent`.

## Develop

```bash
npm install
npm run type-check
npm run lint:primitives
npm run test:run
npm run build

composer install
composer test
```

The PHP suite does not require a live database for route, RBAC and API-doc
parity checks. Database-backed coverage can use `TDS_TEST_DB_DSN` where
available.

## Enable it

Add the manifest to the frontend host's extension list and add
`new BlogCmsModule()` to the base API's enabled modules.
