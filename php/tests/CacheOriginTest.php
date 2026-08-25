<?php
declare(strict_types=1);

namespace Tds\Ext\BlogCms\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tds\Ext\BlogCms\Support\CacheOrigin;

final class CacheOriginTest extends TestCase
{
    /** @return iterable<string,array{string,string}> */
    public static function validOrigins(): iterable
    {
        yield 'https' => ['https://blog.example', 'https://blog.example'];
        yield 'trailing slash' => ['https://blog.example/', 'https://blog.example'];
        yield 'normalised case and port' => ['HTTPS://BLOG.EXAMPLE:8443/', 'https://blog.example:8443'];
    }

    #[DataProvider('validOrigins')]
    public function testNormalisesPureHttpOrigins(string $input, string $expected): void
    {
        self::assertSame($expected, CacheOrigin::normalize($input));
    }

    /** @return iterable<string,array{string}> */
    public static function unsafeOrigins(): iterable
    {
        yield 'userinfo' => ['https://token@blog.example'];
        yield 'password' => ['https://user:pass@blog.example'];
        yield 'path' => ['https://blog.example/tds/cache'];
        yield 'query' => ['https://blog.example?target=other'];
        yield 'fragment' => ['https://blog.example#cache'];
        yield 'wrong scheme' => ['ftp://blog.example'];
        yield 'not a url' => ['blog.example'];
        yield 'blank' => [''];
    }

    #[DataProvider('unsafeOrigins')]
    public function testRejectsAnythingThatIsNotAPureHttpOrigin(string $input): void
    {
        self::assertNull(CacheOrigin::normalize($input));
    }
}
