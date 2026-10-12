<?php
declare(strict_types=1);

namespace Tds\Ext\BlogCms\Support;

use LogicException;
use PDO;
use ReflectionClassConstant;

/**
 * Replaces the text of articles a seed migration has already written — the
 * shared half of every `*_blog_cms_rewrite_*` migration (2026-10 editorial pass).
 *
 * A seed that ran in production never runs again, so a better text cannot be
 * edited into it. A rewrite migration names its seed file (`SEED`) and lists
 * the new title, excerpt, meta description and body per slug and language.
 * The OLD values are read from the seed class itself rather than copied, so
 * the rewrite file holds each article once.
 *
 * Guards, in the spirit of `BlogCmsSeoRefreshMeta`:
 *
 *  - Title, excerpt and body change only on a row still carrying the seeded
 *    title AND body verbatim. An article an editor has touched stays theirs.
 *  - The meta description changes only on a row that now carries the new body
 *    and still the seeded description (after `BlogCmsSeoRefreshMeta`), so an
 *    edited description survives too.
 *  - `revert()` is the same pair of statements with old and new swapped.
 *
 * Slug, language, category, tags and `published_at` are never touched: slugs
 * and tags are URL segments mirrored in other repositories, and `updated_at`
 * moves on its own, which is what the article's `dateModified` reports.
 */
final class PostRewriter
{
    private const MIGRATIONS = __DIR__ . '/../../db/migrations';
    private const META_REFRESH = '20260728000011_blog_cms_seo_refresh_meta.php';

    /** @param list<array{slug:string, lang:string, title:string, excerpt:string, meta:string, body:string}> $posts */
    public static function apply(PDO $conn, string $seedFile, array $posts): void
    {
        self::write($conn, self::pairs($seedFile, $posts), 'new', 'old');
    }

    /** @param list<array{slug:string, lang:string, title:string, excerpt:string, meta:string, body:string}> $posts */
    public static function revert(PDO $conn, string $seedFile, array $posts): void
    {
        self::write($conn, self::pairs($seedFile, $posts), 'old', 'new');
    }

    /**
     * Each rewrite next to the seeded row it replaces.
     *
     * @param list<array<string,string>> $posts
     * @return list<array{old: array<string,string>, new: array<string,string>}>
     */
    public static function pairs(string $seedFile, array $posts): array
    {
        $seeded = [];
        foreach (self::seeded($seedFile) as $post) {
            $seeded[$post['slug'] . '|' . $post['lang']] = $post;
        }

        $pairs = [];
        foreach ($posts as $post) {
            $old = $seeded[$post['slug'] . '|' . $post['lang']] ?? null;
            if ($old === null) {
                throw new LogicException("{$post['slug']} [{$post['lang']}] is not seeded by {$seedFile}");
            }
            $pairs[] = ['old' => $old, 'new' => $post];
        }
        return $pairs;
    }

    /**
     * A seed migration's `POSTS`, with the meta description each row carries
     * once `BlogCmsSeoRefreshMeta` has run.
     *
     * @return list<array<string,string>>
     */
    public static function seeded(string $seedFile): array
    {
        $posts = self::constant($seedFile, 'POSTS');
        $fixes = self::constant(self::META_REFRESH, 'META');
        foreach ($posts as &$post) {
            foreach ($fixes as $fix) {
                if ($fix['slug'] === $post['slug'] && $fix['lang'] === $post['lang'] && $fix['old'] === $post['meta']) {
                    $post['meta'] = $fix['new'];
                }
            }
        }
        unset($post);
        return $posts;
    }

    /**
     * Read a constant from a migration class. Phinx has normally loaded every
     * migration file before the first one runs; the `require_once` covers a
     * caller (a test, a single-file run) where it has not.
     *
     * @return list<array<string,string>>
     */
    private static function constant(string $file, string $name): array
    {
        $class = str_replace('_', '', ucwords((string) preg_replace('/^\d{14}_/', '', basename($file, '.php')), '_'));
        if (!class_exists($class, false)) {
            require_once self::MIGRATIONS . '/' . $file;
        }
        /** @var list<array<string,string>> $value */
        $value = (new ReflectionClassConstant($class, $name))->getValue();
        return $value;
    }

    /** @param list<array{old: array<string,string>, new: array<string,string>}> $pairs */
    private static function write(PDO $conn, array $pairs, string $to, string $from): void
    {
        $text = $conn->prepare(
            'UPDATE blog_post SET title = :t, excerpt = :e, body = :body
              WHERE slug = :s AND lang = :l AND title = :from_t AND body = :from_body'
        );
        $meta = $conn->prepare(
            'UPDATE blog_post SET meta_description = :m
              WHERE slug = :s AND lang = :l AND body = :body AND meta_description = :from_m'
        );

        foreach ($pairs as $pair) {
            $target = $pair[$to];
            $source = $pair[$from];
            $text->execute([
                ':t' => $target['title'],
                ':e' => $target['excerpt'],
                ':body' => $target['body'],
                ':s' => $target['slug'],
                ':l' => $target['lang'],
                ':from_t' => $source['title'],
                ':from_body' => $source['body'],
            ]);
            $meta->execute([
                ':m' => $target['meta'],
                ':s' => $target['slug'],
                ':l' => $target['lang'],
                ':body' => $target['body'],
                ':from_m' => $source['meta'],
            ]);
        }
    }
}
