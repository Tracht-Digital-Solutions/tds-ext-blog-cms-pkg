# Seeded articles

## `BlogCmsSeedPosts` (`20260728000007`)

Ships the six launch articles, DE + EN. A fresh installation has **no `blog` row**, so
`defaultBlog()` is null and the public list stays empty until an operator acts. The seed
therefore:

- creates the default blog when none exists (reusing an existing one, never adding a second the
  public read wouldn't see),
- seeds the `blog_author` byline,
- writes posts with **`draft = 0` and a non-null `published_at`**. Miss either and the rows exist
  but are invisible, with no error.

Properties to preserve:

- **English rows carry `machine_translated = 0`.** They are hand-written. Flagged as machine
  output, `TranslationSync` would overwrite them with DeepL text on the next German save, and the
  frontend would label them as machine-translated.
- **Idempotent by `(blog_id, slug, lang)`.** `down()` deletes only rows still carrying the seeded
  title **and** body verbatim, so an operator's edits survive a rollback. It keeps the blog and
  author rows.
- **Slugs are mirrored outside this repo:** tds-shared's `blog.posts` fallback teasers (used by
  `tds-landingpage-frontend`'s `Journal.astro`) and `tds-blog-frontend`'s `lib/demoContent.ts`.
  Renaming a slug here without changing those publishes 404 links, and no build checks it.

## Every further article is its own migration

`20260728000007` has run in production and Phinx won't execute it again; a new entry in its
`POSTS` array would only reach fresh installations. Copy the pattern of
`BlogCmsSeedPostShopMigration` (`20260728000010`): same row shape, same INSERT, same
idempotency, same verbatim-match `down()`.

## Content corrections

`BlogCmsSeoRefreshMeta` (`20260728000011`) shortened eight `meta_description` values over 160
characters, guarded by `WHERE meta_description = :old`. An edited description is never
overwritten and a second run changes nothing. Guarding on the **old value** is the pattern for
any later correction.

## `php/tests/SeedContentTest.php`

Reads the constants from the migration files (through a stub for Phinx's base class) and checks
what otherwise fails silently: DE/EN completeness, `published_at` shape, tags usable as URL
segments, column limits, effective meta descriptions within 80–160, no dead entry in the refresh
map, `draft = 0` / `machine_translated = 0` in both INSERTs, file-name ↔ class-name mapping and
unique version prefixes.
