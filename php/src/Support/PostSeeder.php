<?php
declare(strict_types=1);

namespace Tds\Ext\BlogCms\Support;

use PDO;

/**
 * Writes seeded articles — the shared half of every `*_blog_cms_seed_post_*`
 * migration since the shop-product guides (2026-10).
 *
 * Each article stays its own migration (a ran migration never runs again, so a
 * new article cannot be appended to an old one), but the four rules that each
 * fail silently live here once instead of in every file:
 *
 *  1. Reuse the existing `blog` row (`ORDER BY name, id LIMIT 1`) and create
 *     one only when there is none — a second blog is invisible to the public
 *     read surface.
 *  2. `draft = 0` AND a non-null `published_at`; `publicPosts()` needs both.
 *  3. One row per language sharing the slug — unique `(blog_id, slug, lang)`.
 *  4. `machine_translated = 0`: the English rows are written by hand, and
 *     flagged as machine output `TranslationSync` would overwrite them with
 *     DeepL text the next time the German article is saved.
 *
 * Idempotent by `(blog_id, slug, lang)`; `remove()` deletes only rows still
 * carrying the seeded title and body verbatim.
 */
final class PostSeeder
{
    private const BLOG_KEY = 'journal';
    private const BLOG_NAME = 'Tracht Digital Journal';
    private const AUTHOR_NAME = 'Julian Tracht';
    private const AUTHOR_BIO = 'Freier Entwickler aus Schwarzenbek bei Hamburg. Baut Websites, Webshops und individuelle Werkzeuge für Selbstständige, kleine Unternehmen und lokale Betriebe.';

    /** The INSERT the seed test checks for `draft, machine_translated)` / `:p, 0, 0)`. */
    public const INSERT_SQL = 'INSERT INTO blog_post
                (blog_id, slug, lang, category, title, excerpt, meta_description, tags,
                 body, cover_hint, author_id, published_at, draft, machine_translated)
             VALUES
                (:b, :s, :l, :c, :t, :e, :m, :g, :body, NULL, :a, :p, 0, 0)';

    /**
     * @param list<array{slug:string, lang:string, category:string, title:string,
     *   excerpt:string, meta:string, tags:string, published:string, body:string}> $posts
     */
    public static function insert(PDO $conn, array $posts): void
    {
        $blogId = $conn->query('SELECT id FROM blog ORDER BY name, id LIMIT 1')->fetchColumn();
        if ($blogId === false || $blogId === null) {
            $conn->prepare('INSERT INTO blog (blog_key, name) VALUES (:k, :n)')
                ->execute([':k' => self::BLOG_KEY, ':n' => self::BLOG_NAME]);
            $blogId = $conn->lastInsertId();
        }
        $blogId = (int) $blogId;

        $authorStmt = $conn->prepare('SELECT id FROM blog_author WHERE name = :n LIMIT 1');
        $authorStmt->execute([':n' => self::AUTHOR_NAME]);
        $authorId = $authorStmt->fetchColumn();
        if ($authorId === false || $authorId === null) {
            $conn->prepare('INSERT INTO blog_author (name, bio) VALUES (:n, :b)')
                ->execute([':n' => self::AUTHOR_NAME, ':b' => self::AUTHOR_BIO]);
            $authorId = $conn->lastInsertId();
        }
        $authorId = $authorId !== false && $authorId !== null ? (int) $authorId : null;

        $exists = $conn->prepare('SELECT id FROM blog_post WHERE blog_id = :b AND slug = :s AND lang = :l LIMIT 1');
        $insert = $conn->prepare(self::INSERT_SQL);
        foreach ($posts as $post) {
            $exists->execute([':b' => $blogId, ':s' => $post['slug'], ':l' => $post['lang']]);
            if ($exists->fetch() !== false) {
                continue;
            }
            $insert->execute([
                ':b' => $blogId,
                ':s' => $post['slug'],
                ':l' => $post['lang'],
                ':c' => $post['category'],
                ':t' => $post['title'],
                ':e' => $post['excerpt'],
                ':m' => $post['meta'],
                ':g' => $post['tags'],
                ':body' => $post['body'],
                ':a' => $authorId,
                ':p' => $post['published'],
            ]);
        }
    }

    /** @param list<array<string,string>> $posts */
    public static function remove(PDO $conn, array $posts): void
    {
        $delete = $conn->prepare('DELETE FROM blog_post WHERE slug = :s AND lang = :l AND title = :t AND body = :body');
        foreach ($posts as $post) {
            $delete->execute([':s' => $post['slug'], ':l' => $post['lang'], ':t' => $post['title'], ':body' => $post['body']]);
        }
    }
}
