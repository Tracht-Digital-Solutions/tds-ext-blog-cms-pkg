# AGENTS.md — tds-ext-blog-cms-pkg

Frontend extension for the **blog CMS**: blogs, posts (DE/EN), authors, DeepL
auto-translation, page-cache refresh and the public read API that the blog and landing page
render from. Ported from `tds-content-api`'s blog model. It contributes a PHP `Module` to
`tds-core-frontend-api` and the `/blog` editor, a settings registry and a widget to the admin
product.

Read `tds-frontend-contract-pkg/AGENTS.md` and `tds-core-frontend-api/AGENTS.md` first.

## Commands

```bash
npm install --no-package-lock   # never npm ci
npm run type-check
npm run lint:primitives
npm run test:run                # vitest
npm run build                   # tsup
composer install && composer test   # phpunit; DB-backed tests need TDS_TEST_DB_DSN
```

## Hard rules

- **Bind container entries unconditionally.** Never guard with `!$c->has(X::class)`.
- Public `/content/*` reads stay read-only, ungated by permissions, and degrade to an empty payload on DB errors.
- Only `draft = 0` rows with a `published_at` are public.
- Never widen `siteKeyRoutes()` to `/content`; never list a route a visitor's browser calls.
- Every new seeded article is its own migration; English seeds carry `machine_translated = 0`.
- Seeded slugs are mirrored in `tds-shared` and `tds-blog-frontend`; rename all or none.
- `renderMarkdown` lives in tds-shared. Never re-inline a local copy.
- Blog registration lives under *Einstellungen → Blog-CMS*; `/blog` only selects and edits.
- Outcomes are toasts; configuration problems stay in-flow. Call the API with `apiFetch`.
- Migrations stay in the `20260728*` band. Don't touch `version`; the admin host pins `^0.3.x`.

## Topic files

| File | Read before |
|---|---|
| [docs/agents/architecture.md](docs/agents/architecture.md) | Changing the model, editor, authors, translation or settings |
| [docs/agents/public-api.md](docs/agents/public-api.md) | Touching `/content/*`, site keys or the page cache |
| [docs/agents/seed-content.md](docs/agents/seed-content.md) | Adding or correcting seeded articles |
| [docs/agents/backend.md](docs/agents/backend.md) | Touching PHP, DI bindings, migrations or `php/docs/api.php` |
| [docs/agents/frontend-conventions.md](docs/agents/frontend-conventions.md) | Touching any island, page or widget markup |
| [docs/agents/testing.md](docs/agents/testing.md) | Writing or changing tests |
| [docs/agents/release.md](docs/agents/release.md) | Releasing or changing CI |

Workspace rules: `../CLAUDE.md`. Cross-repo state: `../MIGRATION-STATUS.md`.
