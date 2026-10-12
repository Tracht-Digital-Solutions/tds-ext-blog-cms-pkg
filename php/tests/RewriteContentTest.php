<?php
declare(strict_types=1);

namespace Tds\Ext\BlogCms\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tds\Ext\BlogCms\Support\PostRewriter;

/**
 * The rewritten articles (`*_blog_cms_rewrite_*`), checked as content.
 *
 * A rewrite that names a row its seed never wrote, drops a product embed or
 * links a tool slug that does not exist updates or renders nothing — no
 * error anywhere. These checks are the only place that notices.
 */
final class RewriteContentTest extends TestCase
{
    private const MIGRATIONS = __DIR__ . '/../db/migrations';
    private const WORKSPACE = __DIR__ . '/../../..';
    private const TOOLS_ORIGIN = 'https://tools.tracht-digital.de';

    /** @var list<array{file:string, seed:string, posts:list<array<string,string>>}> */
    private static array $rewrites = [];

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/stubs/PhinxAbstractMigration.php';

        $files = glob(self::MIGRATIONS . '/*_blog_cms_rewrite_*.php') ?: [];
        sort($files);
        foreach ($files as $path) {
            require_once $path;
            $name = preg_replace('/^\d{14}_/', '', basename($path, '.php'));
            $class = new ReflectionClass(str_replace('_', '', ucwords((string) $name, '_')));
            self::$rewrites[] = [
                'file' => basename($path),
                'seed' => (string) $class->getConstant('SEED'),
                'posts' => $class->getConstant('POSTS'),
            ];
        }
    }

    /** @return list<array<string,string>> */
    private function rewritten(): array
    {
        return array_merge(...array_map(static fn (array $r): array => $r['posts'], self::$rewrites));
    }

    /** Every slug a seed migration writes. @return array<string,true> */
    private function seededSlugs(): array
    {
        $slugs = [];
        foreach (glob(self::MIGRATIONS . '/*_blog_cms_seed_post*.php') ?: [] as $path) {
            foreach (PostRewriter::seeded(basename($path)) as $post) {
                $slugs[$post['slug']] = true;
            }
        }
        return $slugs;
    }

    /** Tool slugs from the tds-tool-* packs checked out next to this repo. @return array<string,true> */
    private function toolSlugs(): array
    {
        $slugs = [];
        foreach (glob(self::WORKSPACE . '/tds-tool-*-pkg/src/index.ts') ?: [] as $file) {
            preg_match_all('/^\s*slug:\s*"([a-z0-9-]+)"/m', (string) file_get_contents($file), $m);
            foreach ($m[1] as $slug) {
                $slugs[$slug] = true;
            }
        }
        return $slugs;
    }

    /** @return list<string> */
    private function productEmbeds(string $body): array
    {
        preg_match_all('/^\{\{(?:produkt|product):([a-z0-9-]+)(?: (?:card|inline|list))?\}\}$/m', $body, $m);
        $embeds = $m[1];
        sort($embeds);
        return $embeds;
    }

    public function testEverySeededRowIsRewrittenOnce(): void
    {
        self::assertNotEmpty(self::$rewrites);

        $expected = [];
        foreach (glob(self::MIGRATIONS . '/*_blog_cms_seed_post*.php') ?: [] as $path) {
            foreach (PostRewriter::seeded(basename($path)) as $post) {
                $expected[] = "{$post['slug']}|{$post['lang']}";
            }
        }
        $actual = array_map(static fn (array $p): string => "{$p['slug']}|{$p['lang']}", $this->rewritten());

        sort($expected);
        sort($actual);
        self::assertSame($expected, $actual);
    }

    public function testEveryRewriteReplacesTextItsSeedWrote(): void
    {
        foreach (self::$rewrites as $rewrite) {
            self::assertFileExists(self::MIGRATIONS . '/' . $rewrite['seed'], $rewrite['file']);
            // Throws on a slug/lang the named seed never wrote: such a row
            // would match no guard and keep its old text.
            foreach (PostRewriter::pairs($rewrite['seed'], $rewrite['posts']) as $pair) {
                $where = "{$pair['new']['slug']} [{$pair['new']['lang']}]";
                self::assertNotSame($pair['old']['body'], $pair['new']['body'], "unchanged body: {$where}");

                // The guards compare against the old values; a product the
                // seed embedded and the rewrite drops loses its affiliate card.
                self::assertSame(
                    $this->productEmbeds($pair['old']['body']),
                    $this->productEmbeds($pair['new']['body']),
                    "product embeds changed: {$where}"
                );
            }
        }
    }

    public function testRewriteFilesGoThroughPostRewriter(): void
    {
        foreach (self::$rewrites as $rewrite) {
            $source = (string) file_get_contents(self::MIGRATIONS . '/' . $rewrite['file']);
            self::assertStringContainsString('PostRewriter::apply(', $source, $rewrite['file']);
            self::assertStringContainsString('PostRewriter::revert(', $source, $rewrite['file']);
        }
    }

    public function testRewrittenFieldsAreSearchReady(): void
    {
        foreach ($this->rewritten() as $post) {
            $where = "{$post['slug']} [{$post['lang']}]";
            self::assertLessThanOrEqual(200, mb_strlen($post['title']), $where);
            self::assertLessThanOrEqual(70, mb_strlen($post['title']), "title too long for a result line: {$where}");
            self::assertNotSame('', trim($post['excerpt']), $where);
            self::assertGreaterThanOrEqual(80, mb_strlen($post['meta']), "meta too short: {$where}");
            self::assertLessThanOrEqual(160, mb_strlen($post['meta']), "meta too long: {$where}");
        }
    }

    public function testEveryArticleAnswersFirstAndEndsWithAnFaq(): void
    {
        foreach ($this->rewritten() as $post) {
            $where = "{$post['slug']} [{$post['lang']}]";
            $lead = $post['lang'] === 'de' ? '**Kurz gesagt:**' : '**In short:**';
            $faq = $post['lang'] === 'de' ? "\n## Häufige Fragen\n" : "\n## Frequently asked questions\n";

            // The opening answer is what answer engines quote; the FAQ section
            // becomes the FAQPage node (tds-blog-frontend `articleFaq.ts`).
            self::assertStringStartsWith($lead, $post['body'], $where);
            self::assertStringContainsString($faq, $post['body'], $where);
            $tail = substr($post['body'], (int) strpos($post['body'], $faq));
            self::assertGreaterThanOrEqual(3, substr_count($tail, "\n### "), "FAQ needs three questions: {$where}");
        }
    }

    public function testToolLinksPointIntoTheirLanguageTree(): void
    {
        $known = $this->toolSlugs();
        $links = 0;
        foreach ($this->rewritten() as $post) {
            preg_match_all('#\]\((https://tools\.tracht-digital\.de[^)\s]*)\)#', $post['body'], $m);
            $prefix = self::TOOLS_ORIGIN . ($post['lang'] === 'de' ? '/tools/' : '/en/tools/');
            foreach ($m[1] as $url) {
                $links++;
                $where = "{$post['slug']} [{$post['lang']}] links {$url}";
                // The tools site mirrors its German tree under /en with the
                // same slugs; a DE link in an EN article switches language.
                self::assertStringStartsWith($prefix, $url, $where);
                $slug = substr($url, strlen($prefix));
                self::assertSame(1, preg_match('/^[a-z0-9-]+$/', $slug), $where);
                if ($known !== []) {
                    self::assertArrayHasKey($slug, $known, "unknown tool: {$where}");
                }
            }
        }
        self::assertGreaterThan(0, $links);
    }

    public function testEveryToolIsLinkedInBothLanguages(): void
    {
        $known = $this->toolSlugs();
        if ($known === []) {
            self::markTestSkipped('the tds-tool-* packs are not checked out next to this repository');
        }

        $linked = ['de' => [], 'en' => []];
        foreach ($this->rewritten() as $post) {
            preg_match_all('#\]\(https://tools\.tracht-digital\.de(?:/en)?/tools/([a-z0-9-]+)\)#', $post['body'], $m);
            foreach ($m[1] as $slug) {
                $linked[$post['lang']][$slug] = true;
            }
        }
        foreach (['de', 'en'] as $lang) {
            $missing = array_diff(array_keys($known), array_keys($linked[$lang]));
            self::assertSame([], array_values($missing), "tools without a {$lang} article link");
        }
    }

    public function testInternalLinksNameASeededArticleInTheirLanguage(): void
    {
        $slugs = $this->seededSlugs();
        foreach ($this->rewritten() as $post) {
            preg_match_all('#\]\((/[^)\s]*)\)#', $post['body'], $m);
            foreach ($m[1] as $path) {
                $where = "{$post['slug']} [{$post['lang']}] links {$path}";
                $pattern = $post['lang'] === 'de' ? '#^/([a-z0-9-]+)$#' : '#^/en/([a-z0-9-]+)$#';
                self::assertSame(1, preg_match($pattern, $path, $hit), $where);
                self::assertArrayHasKey($hit[1], $slugs, "dead article link: {$where}");
                self::assertNotSame($post['slug'], $hit[1], "self link: {$where}");
            }
        }
    }
}
