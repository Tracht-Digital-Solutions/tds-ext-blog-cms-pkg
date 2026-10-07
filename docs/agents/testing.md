# Testing

```bash
npm run test:run   # vitest; islands opt into jsdom per file via a @vitest-environment docblock
composer test      # phpunit
```

CI (`_build.yml`) runs both suites, plus type-check, `lint:primitives` and build, on
every run.

## Test environment against the current tds-shared

tds-shared is a **peer** dependency, so a fresh standalone install may resolve an old
version while every product build composes the current one. Keep these in mind:

- `apiFetch` first consults the host runtime config (`/tds-runtime.json`), so
  `fetch.mock.calls[0]` is that probe. Call **`primeRuntimeConfig(null)`** in
  `beforeEach`; panel products never ship that file, so "absent" matches production.
- `apiFetch` is **async**. `await waitFor(() => expect(fetch).toHaveBeenCalled())`
  before reading `mock.calls`.
- A multipart upload carries an **empty** `headers` object. Assert "no content-type
  header", never "headers is undefined".
- Error-path tests answer with a **populated** body and a non-OK status. Against an
  empty error body the ok-check is unobservable.
- At least one assertion per suite pins the **absolute API host** (see
  [frontend-conventions.md](frontend-conventions.md#api-calls-apifetch-never-a-relative-fetch)).

## Suites

| Suite | Covers |
|---|---|
| `islands/BlogsList.test.tsx` | Blog and post CRUD through the real UI against a URL/method-matching fetch stub: exact request payloads (trimming, `?lang=` on delete), 503/422 copy, selection, targeted cache requests, honest save/cache outcomes |
| `islands/BlogRegistry.test.tsx` | Settings-only registry: immutable key validation, per-blog connection and cache configuration |
| `islands/BlogSettings.test.tsx` | Masked secrets never round-trip to the DOM; blank secret = keep |
| `src/index.test.ts` + `tests/packaging.test.ts` | Every referenced permission declared; every specifier resolves, is in `exports` and in `files` |
| `php/tests/BlogCmsModuleTest.php` | Routes, RBAC and payload validation without a DB |
| `php/tests/BlogRepositoryTest.php` | Repository against a real DB (skips without `TDS_TEST_DB_DSN`) |
| `php/tests/CacheOriginTest.php` | Origin validation |
| `php/tests/SeedContentTest.php` | Seed migrations (see [seed-content.md](seed-content.md)) |
| `php/tests/BlogCmsApiDocsTest.php` | API-doc parity and site-key prefixes |

The escape-first markdown renderer's suite lives in **tds-shared**
(`src/markdown/markdown.test.ts`): script/img/iframe payloads stay inert text, the href
allow-list refuses `javascript:` / `data:` / `vbscript:`, and no tag outside the allow-list
reaches the DOM.

Mutation check: 23 breakages, 23 caught.
