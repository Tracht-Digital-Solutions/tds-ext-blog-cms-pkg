<?php
declare(strict_types=1);

namespace Tds\Ext\BlogCms\Tests;

use BlogCmsSeedPostShopMigration;
use BlogCmsSeedPosts;
use BlogCmsSeoRefreshMeta;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * The seeded articles, checked as content rather than as code.
 *
 * Every rule here is one that fails **silently** in production — an article
 * that never appears, a tag page that 404s, a description Google cuts in half.
 * Nothing in the build noticed any of them until this file existed.
 */
final class SeedContentTest extends TestCase
{
    private const MIGRATIONS = __DIR__ . '/../db/migrations';

    /** @var list<array<string,string>>|null */
    private static ?array $posts = null;

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/stubs/PhinxAbstractMigration.php';
        require_once self::MIGRATIONS . '/20260728000007_blog_cms_seed_posts.php';
        require_once self::MIGRATIONS . '/20260728000010_blog_cms_seed_post_shop_migration.php';
        require_once self::MIGRATIONS . '/20260728000011_blog_cms_seo_refresh_meta.php';

        self::$posts = array_merge(
            (new ReflectionClass(BlogCmsSeedPosts::class))->getConstant('POSTS'),
            (new ReflectionClass(BlogCmsSeedPostShopMigration::class))->getConstant('POSTS'),
        );
    }

    /** @return list<array<string,string>> */
    private function posts(): array
    {
        return self::$posts ?? [];
    }

    /** @return list<array<string,string>> */
    private function metaFixes(): array
    {
        return (new ReflectionClass(BlogCmsSeoRefreshMeta::class))->getConstant('META');
    }

    /** The description each row ends up with once the refresh migration has run. */
    private function effectiveMeta(string $slug, string $lang, string $seeded): string
    {
        foreach ($this->metaFixes() as $fix) {
            if ($fix['slug'] === $slug && $fix['lang'] === $lang && $fix['old'] === $seeded) {
                return $fix['new'];
            }
        }
        return $seeded;
    }

    public function testEveryArticleHasBothLanguages(): void
    {
        $byLang = [];
        foreach ($this->posts() as $post) {
            $byLang[$post['slug']][] = $post['lang'];
        }

        self::assertNotEmpty($byLang);
        foreach ($byLang as $slug => $langs) {
            sort($langs);
            // The unique index is (blog_id, slug, lang): a pair shares its slug
            // and differs only in `lang`. A missing twin is a dead hreflang.
            self::assertSame(['de', 'en'], $langs, "article {$slug}");
        }
    }

    public function testPublishedRowsAreVisible(): void
    {
        foreach ($this->posts() as $post) {
            // `publicPosts()` filters on draft = 0 AND a non-null published_at.
            self::assertNotSame('', trim($post['published']), $post['slug']);
            self::assertSame(
                1,
                preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $post['published']),
                "published_at of {$post['slug']} [{$post['lang']}]"
            );
        }
    }

    public function testTagsAreUsableAsUrlSegments(): void
    {
        foreach ($this->posts() as $post) {
            $where = "{$post['slug']} [{$post['lang']}]";
            self::assertNotSame('', trim($post['tags']), $where);

            foreach (explode(',', $post['tags']) as $tag) {
                $tag = trim($tag);
                // A tag is the URL segment of its tag page verbatim — lowercase,
                // ASCII, hyphenated. An umlaut or a space produces a link the
                // blog cannot resolve, and no build step notices.
                self::assertSame(
                    1,
                    preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $tag),
                    "tag '{$tag}' in {$where}"
                );
            }
        }
    }

    public function testRenderedFieldsFitTheirColumns(): void
    {
        foreach ($this->posts() as $post) {
            $where = "{$post['slug']} [{$post['lang']}]";
            self::assertLessThanOrEqual(120, mb_strlen($post['slug']), $where);
            self::assertLessThanOrEqual(40, mb_strlen($post['category']), $where);
            self::assertLessThanOrEqual(200, mb_strlen($post['title']), $where);
            self::assertLessThanOrEqual(200, mb_strlen($post['tags']), $where);
            self::assertLessThanOrEqual(300, mb_strlen($post['meta']), $where);
            self::assertNotSame('', trim($post['body']), $where);
            self::assertNotSame('', trim($post['excerpt']), $where);
        }
    }

    public function testEffectiveMetaDescriptionsAreSearchReady(): void
    {
        foreach ($this->posts() as $post) {
            $meta = $this->effectiveMeta($post['slug'], $post['lang'], $post['meta']);
            $where = "{$post['slug']} [{$post['lang']}]";

            // Below 80 a description carries nothing; above 160 a search result
            // truncates it mid-sentence. The blog renders this field directly.
            self::assertGreaterThanOrEqual(80, mb_strlen($meta), "meta too short: {$where}");
            self::assertLessThanOrEqual(160, mb_strlen($meta), "meta too long: {$where}");
        }
    }

    public function testEveryMetaFixTargetsARowThatStillCarriesTheOldValue(): void
    {
        foreach ($this->metaFixes() as $fix) {
            $found = false;
            foreach ($this->posts() as $post) {
                if (
                    $post['slug'] === $fix['slug']
                    && $post['lang'] === $fix['lang']
                    && $post['meta'] === $fix['old']
                ) {
                    $found = true;
                }
            }
            // A fix whose `old` no longer matches its seed updates nothing and
            // would quietly leave the long description in place.
            self::assertTrue($found, "dead meta fix for {$fix['slug']} [{$fix['lang']}]");
        }
    }

    public function testSeedInsertsKeepRowsVisibleAndHandWritten(): void
    {
        foreach (['20260728000007_blog_cms_seed_posts.php', '20260728000010_blog_cms_seed_post_shop_migration.php'] as $file) {
            $sql = file_get_contents(self::MIGRATIONS . '/' . $file);
            self::assertIsString($sql);

            // draft = 0 keeps the row public; machine_translated = 0 stops
            // TranslationSync from replacing hand-written English with DeepL
            // output the next time the German article is saved.
            self::assertStringContainsString('draft, machine_translated)', $sql, $file);
            self::assertStringContainsString(':p, 0, 0)', $sql, $file);
        }
    }

    public function testMigrationFileNamesMapToTheirClassNames(): void
    {
        $files = glob(self::MIGRATIONS . '/*.php');
        self::assertIsArray($files);
        self::assertNotEmpty($files);

        $versions = [];
        foreach ($files as $path) {
            $name = basename($path, '.php');
            self::assertSame(1, preg_match('/^(\d{14})_(.+)$/', $name, $m), $name);

            // All composed extensions share one phinxlog: a duplicate version
            // or a name that does not map to its class aborts the whole
            // migration run for every extension, not just this one.
            self::assertArrayNotHasKey($m[1], $versions, "duplicate version {$m[1]}");
            $versions[$m[1]] = $name;

            self::assertStringStartsWith('20260728', $m[1], "outside this module's band: {$name}");

            $expected = str_replace('_', '', ucwords($m[2], '_'));
            $source = file_get_contents($path);
            self::assertIsString($source);
            self::assertStringContainsString(
                "class {$expected} extends AbstractMigration",
                $source,
                "file name {$name} must map to class {$expected}"
            );
        }
    }
}
