<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Where a blog's page cache lives.
 *
 * The public blog renders on demand now and stores each rendered page as a file
 * the web server hands out directly, so a saved article is invisible until its
 * page is rendered again. This column is the origin the save calls to ask for
 * that — `https://blog.tracht-digital.de`, alongside the existing rebuild hook.
 *
 * **Deliberately NOT the same thing as `rebuild_repo`/`rebuild_workflow`.**
 * Those dispatch a CI build and ship *code*; a full build here also re-runs the
 * DeepL translations and re-renders one OG card per post. This re-renders the
 * handful of pages one article dates, in seconds. Both exist because both jobs
 * exist.
 *
 * The matching token is a SECRET and lives in the settings store (namespace
 * `blog-cms`, key `cache_token`), which encrypts at rest.
 *
 * Module-prefixed class name and a version in this module's band: the
 * in-process auto-migrator loads every module's migrations into one process and
 * one phinxlog.
 */
final class AddBlogCmsCacheUrl extends AbstractMigration
{
    public function change(): void
    {
        $this->table('blog')
            ->addColumn('cache_url', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => 'Origin of the public site whose page cache a save rebuilds.',
            ])
            ->update();
    }
}
