<?php
declare(strict_types=1);

namespace Tds\Ext\BlogCms\Tests;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Tds\Ext\BlogCms\Domain\BlogRepository;

/** A PDO double that records the SELECTs without requiring a test database. */
final class RecordingBlogPdo extends PDO
{
    /** @var string[] */
    public array $queries = [];

    public function __construct()
    {
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $this->queries[] = $query;
        return new RecordingBlogStatement();
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $this->queries[] = $query;
        return new RecordingBlogStatement();
    }
}

/** Returns the row shape the real blog table contains after migration 000008. */
final class RecordingBlogStatement extends PDOStatement
{
    /** @var array<string,mixed> */
    private array $row = [
        'id' => 1,
        'blog_key' => 'haupt',
        'name' => 'Hauptblog',
        'rebuild_repo' => null,
        'rebuild_workflow' => null,
        'cache_url' => 'https://blog.example',
        'updated_at' => '2026-08-25 00:00:00',
    ];

    public function execute(?array $params = null): bool
    {
        return true;
    }

    public function fetch(
        int $mode = PDO::FETCH_DEFAULT,
        int $cursorOrientation = PDO::FETCH_ORI_NEXT,
        int $cursorOffset = 0,
    ): mixed {
        return $this->row;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return [$this->row];
    }
}

final class BlogRepositoryTest extends TestCase
{
    public function testRegistryReadsThePageCacheOrigin(): void
    {
        $pdo = new RecordingBlogPdo();
        $blogs = (new BlogRepository($pdo))->blogs();

        self::assertSame('https://blog.example', $blogs[0]['cache_url']);
        self::assertStringContainsString('cache_url', $pdo->queries[0]);
    }

    public function testFindBlogReadsTheOriginUsedBySaveAndManualRebuild(): void
    {
        $pdo = new RecordingBlogPdo();
        $blog = (new BlogRepository($pdo))->findBlog('haupt');

        self::assertSame('https://blog.example', $blog['cache_url'] ?? null);
        self::assertStringContainsString('cache_url', $pdo->queries[0]);
    }
}
