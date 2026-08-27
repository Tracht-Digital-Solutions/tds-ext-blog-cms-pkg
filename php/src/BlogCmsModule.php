<?php
declare(strict_types=1);

namespace Tds\Ext\BlogCms;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Tds\Ext\BlogCms\Domain\BlogRepository;
use Tds\Ext\BlogCms\Service\DeeplTranslator;
use Tds\Ext\BlogCms\Service\TranslationSync;
use Tds\Ext\BlogCms\Support\CacheOrigin;
use Psr\Container\ContainerInterface;
use Tds\Frontend\Contract\AbstractModule;
use Tds\Frontend\Contract\ApiDocSource;
use Tds\Frontend\Contract\CacheEvent;
use Tds\Frontend\Contract\ConnectedSiteCache;
use Tds\Frontend\Contract\PermissionDef;
use Tds\Frontend\Contract\ReportingSiteCache;
use Tds\Frontend\Contract\SettingsStore;
use Tds\Frontend\Contract\SiteCache;
use Tds\Frontend\Contract\SiteConnectionException;
use Tds\Frontend\Contract\SiteConnectionIdentity;
use Tds\Frontend\Contract\SiteConnections;
use Tds\Frontend\Contract\SiteKeyProtected;
use Tds\Frontend\Contract\UserContext;

/**
 * Backend Module for the Blog-CMS (checkpoint-1: blog registry + per-(blog, slug,
 * lang) post CRUD + the posts widget summary). Auth via the core UserContext
 * (`blog:read`/`blog:write`, admins bypass); data via the core PDO. A save
 * triggering a static-blog rebuild lands in a later checkpoint.
 */
final class BlogCmsModule extends AbstractModule implements ApiDocSource, SiteKeyProtected
{
    private const LANGS = ['de', 'en'];

    public function id(): string
    {
        return 'blog-cms';
    }

    /** @return PermissionDef[] */
    public function permissions(): array
    {
        return [
            new PermissionDef('blog:read', 'Blog-Beiträge ansehen', 'blog-cms'),
            new PermissionDef('blog:write', 'Blog-Beiträge bearbeiten', 'blog-cms'),
        ];
    }

    /** @return string[] */
    public function migrations(): array
    {
        return [__DIR__ . '/../db/migrations'];
    }

    public function register(App $app): void
    {
        $c = $app->getContainer();
        // NEVER guard these with `!$c->has(X)`. PHP-DI answers `has()` from its
        // definition sources, and autowiring is one of them: for any *concrete,
        // instantiable* class the answer is always true, whether or not anyone
        // ever bound it. So the guard skipped every one of these bindings and the
        // container silently autowired instead — invisible for the repository
        // (its only argument is the bound PDO, so the object is identical), fatal
        // for the two services below, whose constructors take strings PHP-DI
        // cannot guess. Saving a post 500'd with `Parameter $apiKey of
        // __construct() has no value defined or guessable` and the settings-store
        // factories here never ran at all. The module owns these classes; nothing
        // else defines them, so binding unconditionally is the correct shape.
        $c?->set(BlogRepository::class, static fn ($c) => new BlogRepository($c->get(PDO::class)));
        if ($c !== null) {
            $c->set(TranslationSync::class, static function ($c): TranslationSync {
                $store = self::setting($c);
                // DeepL key: settings store → BLOG_DEEPL_API_KEY → DEEPL_API_KEY.
                $key = $store?->getSecret('blog-cms', 'deepl_api_key');
                if ($key === null || $key === '') {
                    $key = (string) (getenv('BLOG_DEEPL_API_KEY') ?: getenv('DEEPL_API_KEY') ?: '');
                }
                // Auto-translate flag: settings store ("0" disables) → env → default on.
                $flag = $store?->get('blog-cms', 'auto_translate');
                if ($flag === null) {
                    $envFlag = getenv('BLOG_AUTO_TRANSLATE');
                    $flag = $envFlag === false ? '1' : (string) $envFlag;
                }
                $enabled = !in_array(strtolower($flag), ['0', 'false', 'no', 'off'], true);
                return new TranslationSync($c->get(BlogRepository::class), new DeeplTranslator($key), $enabled);
            });
        }

        // --- Public read surface (UNAUTHENTICATED) --------------------------
        // The successor to tds-content-api's open `/content/blog*` read that the
        // public blog + landingpage SSG builds fetch at build time. Serves only
        // PUBLISHED posts of the single default blog, in the camelCase BlogPost
        // shape tds-shared defines (markdown body → the frontend renders it).
        // These are the ONLY ungated routes in this module; keep them read-only.
        $app->get('/content/blog', function (Request $req, Response $res) use ($c): Response {
            // Graceful for a build-fetch: any DB hiccup returns an empty page so
            // the public build falls back to its baked defaults, never a 500.
            try {
                $repo = $c->get(BlogRepository::class);
                $blog = self::requestBlog($c, $repo);
                if ($blog === null) {
                    return self::json($res, ['posts' => [], 'nextCursor' => null]);
                }
                $q = $req->getQueryParams();
                $lang = self::publicLang($q['lang'] ?? null);
                $limit = max(1, min((int) ($q['limit'] ?? 50), 100));
                $cursor = isset($q['cursor']) && $q['cursor'] !== '' ? (int) $q['cursor'] : null;
                $rows = $repo->publicPosts((int) $blog['id'], $lang, $limit, $cursor);
                $nextCursor = null;
                if (count($rows) > $limit) {
                    $rows = array_slice($rows, 0, $limit);
                    $nextCursor = (int) $rows[count($rows) - 1]['id'];
                }
                return self::json($res, [
                    'posts' => array_map([self::class, 'publicShape'], $rows),
                    'nextCursor' => $nextCursor,
                ]);
            } catch (\Throwable) {
                return self::json($res, ['posts' => [], 'nextCursor' => null]);
            }
        });

        // Popularity: this module has no per-post view counter, so "popular"
        // degrades to newest-first (the frontend just needs a populated tab).
        $app->get('/content/blog/popular', function (Request $req, Response $res) use ($c): Response {
            try {
                $repo = $c->get(BlogRepository::class);
                $blog = self::requestBlog($c, $repo);
                if ($blog === null) {
                    return self::json($res, ['posts' => []]);
                }
                $q = $req->getQueryParams();
                $lang = self::publicLang($q['lang'] ?? null);
                $limit = max(1, min((int) ($q['limit'] ?? 6), 100));
                $rows = array_slice($repo->publicPosts((int) $blog['id'], $lang, $limit, null), 0, $limit);
                return self::json($res, ['posts' => array_map([self::class, 'publicShape'], $rows)]);
            } catch (\Throwable) {
                return self::json($res, ['posts' => []]);
            }
        });

        $app->get('/content/blog/{slug:[a-z0-9-]+}', function (Request $req, Response $res, array $args) use ($c): Response {
            try {
                $repo = $c->get(BlogRepository::class);
                $blog = self::requestBlog($c, $repo);
                $lang = self::publicLang(($req->getQueryParams()['lang'] ?? null)) ?? 'de';
                $row = $blog === null ? null : $repo->publicPost((int) $blog['id'], (string) $args['slug'], $lang);
                if ($row === null) {
                    return self::json($res, ['error' => 'Post not found'], 404);
                }
                return self::json($res, ['post' => self::publicShape($row)]);
            } catch (\Throwable) {
                return self::json($res, ['error' => 'Post not found'], 404);
            }
        });

        // Curated topics + custom snippets were tds-content-api features with no
        // equivalent in this module — answer with the "nothing maintained" shape
        // the frontends already treat as a fallback (null topics / empty snippets)
        // so their build stays green.
        $app->get('/content/topics', function (Request $req, Response $res): Response {
            $lang = self::publicLang($req->getQueryParams()['lang'] ?? null) ?? 'de';
            return self::json($res, ['lang' => $lang, 'topics' => null]);
        });
        $app->get('/content/snippets', function (Request $req, Response $res): Response {
            return self::json($res, ['snippets' => []]);
        });

        $app->get('/blog/summary', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:read', $res)) !== null) {
                return $deny;
            }
            return self::json($res, ['posts' => $c->get(BlogRepository::class)->postCount()]);
        });

        $app->get('/blogs', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:read', $res)) !== null) {
                return $deny;
            }
            return self::json($res, ['blogs' => $c->get(BlogRepository::class)->blogs()]);
        });

        $app->post('/blogs', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:write', $res)) !== null) {
                return $deny;
            }
            $body = (array) $req->getParsedBody();
            $key = strtolower(trim((string) ($body['blog_key'] ?? '')));
            $name = trim((string) ($body['name'] ?? ''));
            if (preg_match('/^[a-z0-9-]{2,64}$/', $key) !== 1 || $name === '') {
                return self::json($res, ['error' => 'blog_key (kebab) and name are required'], 422);
            }
            $repo = $c->get(BlogRepository::class);
            if ($repo->blogKeyExists($key)) {
                return self::json($res, ['error' => 'blog_key already exists'], 409);
            }
            return self::json($res, ['id' => $repo->createBlog($key, $name)], 201);
        });

        $app->get('/blogs/{blog:[a-z0-9-]+}/connection', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:read', $res)) !== null) {
                return $deny;
            }
            if ($c->get(BlogRepository::class)->findBlog((string) $args['blog']) === null) {
                return self::json($res, ['error' => 'Blog not found'], 404);
            }
            $connections = self::connections($c);
            if ($connections === null) {
                return self::json($res, ['error' => 'Site connection service is not available'], 503);
            }
            $connection = $connections->get('blog', (string) $args['blog']);
            return $connection === null
                ? self::json($res, ['error' => 'Connection not found'], 404)
                : self::json($res, ['connection' => $connection->toArray()]);
        });

        $app->delete('/blogs/{blog:[a-z0-9-]+}/connection', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:write', $res)) !== null) {
                return $deny;
            }
            if ($c->get(BlogRepository::class)->findBlog((string) $args['blog']) === null) {
                return self::json($res, ['error' => 'Blog not found'], 404);
            }
            $connections = self::connections($c);
            if ($connections === null) {
                return self::json($res, ['error' => 'Site connection service is not available'], 503);
            }
            return self::json($res, ['ok' => true, 'deleted' => $connections->delete('blog', (string) $args['blog'])]);
        });

        $app->post('/blogs/{blog:[a-z0-9-]+}/connection/pairing', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:write', $res)) !== null) {
                return $deny;
            }
            $repo = $c->get(BlogRepository::class);
            if ($repo->findBlog((string) $args['blog']) === null) {
                return self::json($res, ['error' => 'Blog not found'], 404);
            }
            $connections = self::connections($c);
            if ($connections === null) {
                return self::json($res, ['error' => 'Site connection service is not available'], 503);
            }
            $body = (array) $req->getParsedBody();
            $origin = trim((string) ($body['origin'] ?? ''));
            $provided = is_array($body['bindings'] ?? null) ? $body['bindings'] : [];
            $bindings = ['blog' => (string) $args['blog']];
            $candidates = self::bindingKeys($c, 'cms_site', 'site_key');
            $website = trim((string) ($provided['website'] ?? ''));
            if ($website !== '') {
                if (!in_array($website, $candidates, true)) {
                    return self::json($res, [
                        'error' => 'Der gewählte Website-Schlüssel existiert nicht.',
                        'candidates' => $candidates,
                    ], 422);
                }
                $bindings['website'] = $website;
            } else {
                if (count($candidates) === 1) {
                    $bindings['website'] = $candidates[0];
                } elseif (count($candidates) > 1) {
                    return self::json($res, [
                        'error' => 'Bei mehreren Websites muss der Website-Schlüssel gewählt werden.',
                        'candidates' => $candidates,
                    ], 422);
                }
            }
            try {
                $pairing = $connections->createPairing(
                    'blog',
                    (string) $args['blog'],
                    $origin,
                    'blog',
                    $bindings,
                    ['/content/blog', '/content/topics', '/content/snippets', '/content/landing'],
                );
                return self::json($res, $connections->deliverPairing($pairing, self::apiBase($req))->toArray(), 201);
            } catch (SiteConnectionException $e) {
                return self::json($res, ['error' => $e->getMessage(), 'code' => $e->errorCode], $e->httpStatus);
            } catch (\Throwable $e) {
                error_log('[blog-cms] pairing failed: ' . $e->getMessage());
                return self::json($res, ['error' => 'Pairing could not be created'], 503);
            }
        });

        // --- authors (byline registry, blog-agnostic) -------------------------
        $app->get('/blog/authors', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:read', $res)) !== null) {
                return $deny;
            }
            return self::json($res, ['authors' => $c->get(BlogRepository::class)->authors()]);
        });

        $app->post('/blog/authors', function (Request $req, Response $res) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:write', $res)) !== null) {
                return $deny;
            }
            $body = (array) $req->getParsedBody();
            $name = trim((string) ($body['name'] ?? ''));
            if (mb_strlen($name) < 2) {
                return self::json($res, ['error' => 'name is required'], 422);
            }
            $bio = self::optional($body['bio'] ?? null, 500);
            $avatar = self::optional($body['avatar_url'] ?? null, 500);
            $repo = $c->get(BlogRepository::class);
            // A user_id ties the byline to a panel user (one snapshot per user);
            // absent ⇒ a free-form / guest author.
            $userId = (int) ($body['user_id'] ?? 0);
            $id = $userId > 0
                ? $repo->upsertAuthorFromUser($userId, $name, $bio, $avatar)
                : $repo->createAuthor($name, $bio, $avatar);
            return self::json($res, ['id' => $id], 201);
        });

        $app->delete('/blog/authors/{id:[0-9]+}', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:write', $res)) !== null) {
                return $deny;
            }
            $c->get(BlogRepository::class)->deleteAuthor((int) $args['id']);
            return self::json($res, ['ok' => true]);
        });

        $app->get('/blogs/{blog:[a-z0-9-]+}/posts', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:read', $res)) !== null) {
                return $deny;
            }
            $repo = $c->get(BlogRepository::class);
            $blog = $repo->findBlog((string) $args['blog']);
            if ($blog === null) {
                return self::json($res, ['error' => 'Blog not found'], 404);
            }
            return self::json($res, ['posts' => $repo->posts((int) $blog['id'])]);
        });

        $app->get('/blogs/{blog:[a-z0-9-]+}/posts/{slug:[a-z0-9-]+}', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:read', $res)) !== null) {
                return $deny;
            }
            $repo = $c->get(BlogRepository::class);
            $blog = $repo->findBlog((string) $args['blog']);
            if ($blog === null) {
                return self::json($res, ['error' => 'Blog not found'], 404);
            }
            $post = $repo->getPost((int) $blog['id'], (string) $args['slug'], self::lang($req->getQueryParams()['lang'] ?? 'de'));
            if ($post === null) {
                return self::json($res, ['error' => 'Post not found'], 404);
            }
            return self::json($res, $post);
        });

        $app->put('/blogs/{blog:[a-z0-9-]+}/posts/{slug:[a-z0-9-]+}', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:write', $res)) !== null) {
                return $deny;
            }
            $repo = $c->get(BlogRepository::class);
            $blog = $repo->findBlog((string) $args['blog']);
            if ($blog === null) {
                return self::json($res, ['error' => 'Blog not found'], 404);
            }
            $body = (array) $req->getParsedBody();
            $title = trim((string) ($body['title'] ?? ''));
            $content = trim((string) ($body['body'] ?? ''));
            if ($title === '' || $content === '') {
                return self::json($res, ['error' => 'title and body are required'], 422);
            }
            $draft = (bool) ($body['draft'] ?? true);
            $lang = self::lang($body['lang'] ?? 'de');
            // Author is optional; an unknown id is dropped rather than rejected.
            $authorId = (int) ($body['author_id'] ?? 0);
            if ($authorId > 0 && !$repo->authorExists($authorId)) {
                $authorId = 0;
            }
            $data = [
                'category' => trim((string) ($body['category'] ?? 'allgemein')) ?: 'allgemein',
                'title' => $title,
                'excerpt' => (string) ($body['excerpt'] ?? ''),
                'meta_description' => self::optional($body['meta_description'] ?? null, 300),
                'tags' => self::optional($body['tags'] ?? null, 200),
                'body' => $content,
                'cover_hint' => isset($body['cover_hint']) && $body['cover_hint'] !== '' ? (string) $body['cover_hint'] : null,
                'author_id' => $authorId > 0 ? $authorId : null,
                'draft' => $draft,
                // Publishing sets published_at when it's a non-draft with none yet.
                'published_at' => $draft ? null : ($body['published_at'] ?? date('Y-m-d H:i:s')),
                // A manual save is authored content — clears any machine-translated flag.
                'machine_translated' => false,
            ];
            $repo->upsertPost((int) $blog['id'], (string) $args['slug'], $lang, $data);
            // Auto-translate the counterpart language (best-effort, published only).
            $translated = $c->get(TranslationSync::class)->afterSave((int) $blog['id'], (string) $args['slug'], $lang, $data);
            $cache = self::emptyCacheReport('skipped');
            if (!$draft) {
                // Both languages when the counterpart was machine-translated in
                // the same call: the English article changed too, and rebuilding
                // only the saved language leaves it showing the old translation.
                $cache = self::fireCache($c, $blog, [$translated
                    ? new CacheEvent('post', (string) $args['slug'])
                    : new CacheEvent('post', (string) $args['slug'], $lang)]);
            }
            return self::json($res, array_merge(['ok' => true, 'translated' => $translated], $cache));
        });

        // Catch up translations for pre-existing posts of a blog (button in tds-admin).
        $app->post('/blogs/{blog:[a-z0-9-]+}/translations/backfill', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:write', $res)) !== null) {
                return $deny;
            }
            $repo = $c->get(BlogRepository::class);
            $blog = $repo->findBlog((string) $args['blog']);
            if ($blog === null) {
                return self::json($res, ['error' => 'Blog not found'], 404);
            }
            $sync = $c->get(TranslationSync::class);
            if (!$sync->active()) {
                return self::json($res, ['error' => 'Auto-translation is not configured'], 503);
            }
            $created = 0;
            $skipped = 0;
            foreach ($repo->posts((int) $blog['id']) as $meta) {
                // Machine rows are targets, not sources; drafts have nothing to mirror.
                if ((int) ($meta['machine_translated'] ?? 0) === 1 || (int) ($meta['draft'] ?? 1) === 1) {
                    $skipped++;
                    continue;
                }
                $full = $repo->getPost((int) $blog['id'], (string) $meta['slug'], (string) $meta['lang']);
                if ($full === null) {
                    $skipped++;
                    continue;
                }
                $wrote = $sync->afterSave((int) $blog['id'], (string) $meta['slug'], (string) $meta['lang'], [
                    'category' => (string) ($full['category'] ?? 'allgemein'),
                    'title' => (string) ($full['title'] ?? ''),
                    'excerpt' => (string) ($full['excerpt'] ?? ''),
                    'meta_description' => isset($full['meta_description']) ? (string) $full['meta_description'] : null,
                    'tags' => isset($full['tags']) ? (string) $full['tags'] : null,
                    'body' => (string) ($full['body'] ?? ''),
                    'cover_hint' => isset($full['cover_hint']) ? (string) $full['cover_hint'] : null,
                    'author_id' => isset($full['author_id']) ? (int) $full['author_id'] : null,
                    'draft' => false,
                    'published_at' => $full['published_at'] ?? null,
                ]);
                $wrote ? $created++ : $skipped++;
            }
            if ($created > 0) {
                $cache = self::fireCache($c, $blog, [new CacheEvent('post')]);
            } else {
                $cache = self::emptyCacheReport('skipped');
            }
            return self::json($res, array_merge(['created' => $created, 'translation_skipped' => $skipped], $cache));
        });

        // Rebuild a blog's PAGE CACHE ("Seiten-Cache neu bauen").
        //
        // This re-renders pages from content already stored; it never deploys.
        $app->post('/blogs/{blog:[a-z0-9-]+}/cache/rebuild', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:write', $res)) !== null) {
                return $deny;
            }
            $repo = $c->get(BlogRepository::class);
            $blog = $repo->findBlog((string) $args['blog']);
            if ($blog === null) {
                return self::json($res, ['error' => 'Blog not found'], 404);
            }
            if (self::connection($c, 'blog', (string) $args['blog']) === null) {
                $legacyOrigin = trim((string) ($blog['cache_url'] ?? ''));
                if ($legacyOrigin === '') {
                    return self::json($res, ['error' => 'This blog is not connected'], 503);
                }
                if (CacheOrigin::normalize($legacyOrigin) === null) {
                    return self::json($res, ['error' => 'The configured legacy cache origin is invalid'], 422);
                }
            }
            $body = (array) $req->getParsedBody();
            $slug = isset($body['slug']) ? trim((string) $body['slug']) : '';
            $lang = isset($body['lang']) ? self::lang($body['lang']) : null;
            $cache = self::fireCache($c, $blog, [new CacheEvent('post', $slug !== '' ? $slug : null, $lang)]);
            return self::json($res, array_merge(['ok' => $cache['cached']], $cache), self::manualCacheStatus($cache));
        });

        $app->delete('/blogs/{blog:[a-z0-9-]+}/posts/{slug:[a-z0-9-]+}', function (Request $req, Response $res, array $args) use ($c): Response {
            if (($deny = self::require($c->get(UserContext::class), 'blog:write', $res)) !== null) {
                return $deny;
            }
            $repo = $c->get(BlogRepository::class);
            $blog = $repo->findBlog((string) $args['blog']);
            if ($blog === null) {
                return self::json($res, ['error' => 'Blog not found'], 404);
            }
            $lang = self::lang($req->getQueryParams()['lang'] ?? 'de');
            $repo->deletePost((int) $blog['id'], (string) $args['slug'], $lang);
            // A machine-translated counterpart was derived from this row — drop it too.
            $c->get(TranslationSync::class)->afterDelete((int) $blog['id'], (string) $args['slug'], $lang);
            $cache = self::fireCache($c, $blog, [new CacheEvent('post', (string) $args['slug'], $lang)]);
            return self::json($res, array_merge(['ok' => true], $cache));
        });
    }

    // --- helpers ---------------------------------------------------------------

    /**
     * Ask the public blog to re-render the pages a content change affects.
     *
     * Never throws and never fails the save: a site that is down, moved or not
     * configured yet must not turn "publish this article" into an error. The
     * article is stored either way and the panel has a rebuild button.
     *
     * `has()` is legitimate here because SiteCache is an INTERFACE — the base
     * either bound an implementation or it did not. On a concrete class the
     * same check always answers true (PHP-DI autowires), which is the trap the
     * binding comments in this module document.
     *
     * Returns whether a request actually went out, so the editor can say the
     * truth after a save. Reporting "der Artikel wird neu gebaut" on a blog
     * with no cache URL is a cheerful success for a request nobody sent.
     *
     * @param array<string,mixed> $blog
     * @param CacheEvent[] $events
     * @return array{cache_status:string,cached:bool,rebuilt:array,skipped:array,failed:array,unknownEvents:array}
     */
    private static function fireCache(ContainerInterface $c, array $blog, array $events): array
    {
        if ($events === []) {
            return self::emptyCacheReport('skipped');
        }
        $connection = self::connection($c, 'blog', (string) $blog['blog_key']);
        if ($connection !== null && $c->has(ConnectedSiteCache::class)) {
            try {
                $cache = $c->get(ConnectedSiteCache::class);
                $reports = [];
                foreach ($events as $event) {
                    $reports[] = $cache->refresh('blog', (string) $blog['blog_key'], $event)->toArray();
                }
                return self::mergeCacheReports($reports);
            } catch (\Throwable $e) {
                error_log('[blog-cms] connected cache refresh failed: ' . $e->getMessage());
                $report = self::emptyCacheReport('failed');
                $report['failed'][] = ['reason' => 'transport_error'];
                return $report;
            }
        }
        if ($connection !== null) {
            return self::emptyCacheReport('not_configured');
        }
        if (!$c->has(SiteCache::class)) {
            return self::emptyCacheReport('not_configured');
        }
        $url = CacheOrigin::normalize((string) ($blog['cache_url'] ?? ''));
        if ($url === null) {
            return self::emptyCacheReport('not_configured');
        }
        $token = self::setting($c)?->getSecret('blog-cms', 'cache_token');
        if ($token === null || $token === '') {
            $token = (string) (getenv('BLOG_CACHE_TOKEN') ?: '');
        }

        $cache = $c->get(SiteCache::class);
        // Ask first: `rebuild()` is a documented no-op without a token, and a
        // no-op reported as a rebuild is the same lie as a missing URL.
        if (!$cache->isConfigured($url, $token)) {
            return self::emptyCacheReport('not_configured');
        }
        if ($cache instanceof ReportingSiteCache) {
            return $cache->rebuildWithResult($url, $token, $events)->toArray();
        }
        $cache->rebuild($url, $token, $events);
        $report = self::emptyCacheReport('skipped');
        $report['unknownEvents'][] = ['reason' => 'legacy_transport_has_no_result'];
        return $report;
    }

    private static function connections(ContainerInterface $c): ?SiteConnections
    {
        try {
            return $c->has(SiteConnections::class) ? $c->get(SiteConnections::class) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function connection(ContainerInterface $c, string $type, string $id): mixed
    {
        try {
            return self::connections($c)?->get($type, $id);
        } catch (\Throwable) {
            return null;
        }
    }

    /** A keyed request must resolve its explicit blog; only a keyless request may use the legacy default. */
    private static function requestBlog(ContainerInterface $c, BlogRepository $repo): ?array
    {
        try {
            if (!$c->has(SiteConnectionIdentity::class)) {
                return $repo->defaultBlog();
            }
            $identity = $c->get(SiteConnectionIdentity::class);
            if (!$identity->isConnected()) {
                return $repo->defaultBlog();
            }
            $key = $identity->resourceType === 'blog'
                ? $identity->resourceId
                : $identity->binding('blog');
            if (!is_string($key) || trim($key) === '') {
                return null;
            }
            return ctype_digit($key) ? $repo->findBlogById((int) $key) : $repo->findBlog($key);
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return list<string> */
    private static function bindingKeys(ContainerInterface $c, string $table, string $column): array
    {
        try {
            $rows = $c->get(PDO::class)->query("SELECT {$column} FROM {$table} ORDER BY {$column}")->fetchAll(PDO::FETCH_COLUMN);
            return array_values(array_filter(array_map('strval', $rows), static fn (string $v): bool => $v !== ''));
        } catch (\Throwable) {
            return [];
        }
    }

    private static function apiBase(Request $req): string
    {
        $uri = $req->getUri();
        return $uri->getScheme() . '://' . $uri->getAuthority();
    }

    /** @return array{cache_status:string,cached:bool,rebuilt:array,skipped:array,failed:array,unknownEvents:array} */
    private static function emptyCacheReport(string $status): array
    {
        return [
            'cache_status' => $status,
            'cached' => false,
            'rebuilt' => [],
            'skipped' => [],
            'failed' => [],
            'unknownEvents' => [],
        ];
    }

    /** @param list<array<string,mixed>> $reports */
    private static function mergeCacheReports(array $reports): array
    {
        if ($reports === []) {
            return self::emptyCacheReport('skipped');
        }
        $merged = self::emptyCacheReport('refreshed');
        $statuses = [];
        $merged['cached'] = true;
        foreach ($reports as $report) {
            $statuses[] = (string) ($report['cache_status'] ?? 'failed');
            $merged['cached'] = $merged['cached'] && (bool) ($report['cached'] ?? false);
            foreach (['rebuilt', 'skipped', 'failed', 'unknownEvents'] as $key) {
                $values = $report[$key] ?? [];
                if (is_array($values)) {
                    $merged[$key] = array_merge($merged[$key], $values);
                }
            }
        }
        if (in_array('failed', $statuses, true)) {
            $merged['cache_status'] = 'failed';
        } elseif (in_array('not_configured', $statuses, true)) {
            $merged['cache_status'] = count(array_unique($statuses)) === 1 ? 'not_configured' : 'failed';
        } elseif (!$merged['cached'] || in_array('skipped', $statuses, true)) {
            $merged['cache_status'] = 'skipped';
        }
        return $merged;
    }

    /** @param array{cache_status:string,cached:bool} $report */
    private static function manualCacheStatus(array $report): int
    {
        return match ($report['cache_status']) {
            'refreshed' => 202,
            'not_configured' => 503,
            default => 502,
        };
    }

    private static function require(UserContext $user, string $permission, Response $res): ?Response
    {
        if (!$user->isAuthenticated()) {
            return self::json($res, ['error' => 'Unauthorized'], 401);
        }
        if (!$user->has($permission)) {
            return self::json($res, ['error' => 'Forbidden'], 403);
        }
        return null;
    }

    private static function lang(mixed $value): string
    {
        $v = is_string($value) ? strtolower($value) : '';
        return in_array($v, self::LANGS, true) ? $v : 'de';
    }

    /** Nullable lang for the public read routes (absent/invalid → null = both langs). */
    private static function publicLang(mixed $value): ?string
    {
        $v = is_string($value) ? strtolower($value) : '';
        return in_array($v, self::LANGS, true) ? $v : null;
    }

    /**
     * Map a DB row to the camelCase `BlogPost` shape tds-shared defines (the
     * contract the public blog/landingpage consume). `body` (+ created/updated)
     * is present only on the single-post read. Fields this module has no column
     * for (viewCount, adsMode, block bodyFormat) are omitted — all optional in
     * `BlogPost`, so the frontend applies its defaults (view 0, ads "default",
     * markdown body).
     *
     * @param array<string,mixed> $r
     * @return array<string,mixed>
     */
    private static function publicShape(array $r): array
    {
        $author = ($r['author_id'] ?? null) !== null ? [
            'id' => (int) $r['author_id'],
            'name' => (string) ($r['author_name'] ?? ''),
            'slug' => self::slugify((string) ($r['author_name'] ?? '')),
            'avatarUrl' => ($r['author_avatar_url'] ?? null) !== null ? (string) $r['author_avatar_url'] : null,
            'bio' => ($r['author_bio'] ?? null) !== null ? (string) $r['author_bio'] : null,
        ] : null;

        $out = [
            'id' => (int) $r['id'],
            'slug' => (string) $r['slug'],
            'lang' => (string) $r['lang'],
            'category' => (string) ($r['category'] ?? ''),
            'title' => (string) $r['title'],
            'excerpt' => (string) ($r['excerpt'] ?? ''),
            'tags' => ($r['tags'] ?? null) !== null ? (string) $r['tags'] : '',
            'coverHint' => ($r['cover_hint'] ?? null) !== null ? (string) $r['cover_hint'] : null,
            'publishedAt' => ($r['published_at'] ?? null) !== null ? (string) $r['published_at'] : null,
            'draft' => false,
            'machineTranslated' => (bool) ($r['machine_translated'] ?? false),
            'authorId' => ($r['author_id'] ?? null) !== null ? (int) $r['author_id'] : null,
            'author' => $author,
        ];
        // Full-post read carries the body + timestamps + SEO meta.
        if (array_key_exists('body', $r)) {
            $out['body'] = (string) $r['body'];
            $out['bodyFormat'] = 'markdown';
            $out['metaDescription'] = ($r['meta_description'] ?? null) !== null ? (string) $r['meta_description'] : null;
            $out['createdAt'] = (string) ($r['created_at'] ?? $r['published_at'] ?? '');
            $out['updatedAt'] = (string) ($r['updated_at'] ?? $r['published_at'] ?? '');
        }
        return $out;
    }

    /** Best-effort URL slug from an author name (this module stores no slug column). */
    private static function slugify(string $name): string
    {
        $s = strtolower(trim($name));
        $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
        return trim($s, '-');
    }

    private static function optional(mixed $value, int $limit): ?string
    {
        $v = trim((string) ($value ?? ''));
        return $v === '' ? null : mb_substr($v, 0, $limit);
    }

    /**
     * The core's settings store if the base bound it (it resolves the contract
     * interface), else null — so an isolated unit test (no core) falls back to env.
     */
    private static function setting(\Psr\Container\ContainerInterface $c): ?SettingsStore
    {
        return $c->has(SettingsStore::class) ? $c->get(SettingsStore::class) : null;
    }

    private static function json(Response $res, mixed $data, int $status = 200): Response
    {
        $res->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));
        return $res->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

    /**
     * Route documentation for the admin frontend's API reference. Kept in its
     * own file so the prose does not sit in the middle of the wiring.
     *
     * @return list<array<string, mixed>>
     */
    public function apiDocs(): array
    {
        return require __DIR__ . '/../docs/api.php';
    }

    /**
     * The routes the public blog + landingpage read at BUILD time, and the only
     * ones a site key may be required for.
     *
     * Prefixes, not patterns — `/content/blog` also covers
     * `/content/blog/{slug}` and `/content/blog/popular`, which is what we want:
     * protecting the listing while leaving every article body open would be a
     * gate in name only.
     *
     * Deliberately NOT `/content`: that would also cover website-cms's
     * `/content/landing` and `/content/legal`, i.e. this module would silently
     * be gating another module's surface — and would stop doing so the day
     * website-cms moved its routes, with nothing to notice.
     *
     * Nothing here is read by a visitor's browser. The contact form, the
     * live-chat widget and the account menu all live on other paths; listing one
     * of those would turn `enforce` into an outage on the public site.
     *
     * @return list<string>
     */
    public function siteKeyRoutes(): array
    {
        return ['/content/blog', '/content/topics', '/content/snippets'];
    }
}
