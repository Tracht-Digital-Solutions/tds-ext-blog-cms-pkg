<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/** Runtime content changes no longer dispatch GitHub workflows. */
final class BlogCmsRemoveGithubRuntime extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('blog')) {
            $table = $this->table('blog');
            if ($table->hasColumn('rebuild_repo')) {
                $table->removeColumn('rebuild_repo');
            }
            if ($table->hasColumn('rebuild_workflow')) {
                $table->removeColumn('rebuild_workflow');
            }
            $table->update();
        }
        if ($this->hasTable('app_setting')) {
            $this->execute("DELETE FROM app_setting WHERE namespace = 'blog-cms' AND skey = 'rebuild_token'");
        }
    }

    public function down(): void
    {
        if ($this->hasTable('blog')) {
            $table = $this->table('blog');
            if (!$table->hasColumn('rebuild_repo')) {
                $table->addColumn('rebuild_repo', 'string', ['limit' => 150, 'null' => true]);
            }
            if (!$table->hasColumn('rebuild_workflow')) {
                $table->addColumn('rebuild_workflow', 'string', ['limit' => 100, 'null' => true]);
            }
            $table->update();
        }
    }
}
