<?php

namespace LekTrail\Tests\Dashboard;

use LekTrail\Dashboard\ReadingFilter;
use PHPUnit\Framework\TestCase;

class ReadingFilterTest extends TestCase
{
    public function testGlobalCacheKey(): void
    {
        $filter = new ReadingFilter();
        $this->assertEquals('_global', $filter->cacheKey());
    }

    public function testCategoryCacheKey(): void
    {
        $filter = new ReadingFilter('php');
        $this->assertEquals('cat:php', $filter->cacheKey());
    }

    public function testYearCacheKey(): void
    {
        $filter = new ReadingFilter(null, 2025);
        $this->assertEquals('year:2025', $filter->cacheKey());
    }

    public function testCombinedCacheKey(): void
    {
        $filter = new ReadingFilter('php', 2025);
        $this->assertEquals('cat:php|year:2025', $filter->cacheKey());
    }

    public function testFromArrayWithAllAttributes(): void
    {
        $filter = ReadingFilter::fromArray([
            'category' => 'tutorials',
            'year' => '2025',
            'status' => 'read',
        ]);

        $this->assertEquals('tutorials', $filter->categorySlug());
        $this->assertEquals(2025, $filter->year());
        $this->assertEquals('read', $filter->status());
    }

    public function testFromArrayWithEmptyAttributes(): void
    {
        $filter = ReadingFilter::fromArray([]);

        $this->assertNull($filter->categorySlug());
        $this->assertNull($filter->year());
        $this->assertNull($filter->status());
    }

    public function testFromArrayIgnoresInvalidYear(): void
    {
        $filter = ReadingFilter::fromArray(['year' => '99']);
        $this->assertNull($filter->year());
    }

    public function testFromArrayIgnoresInvalidStatus(): void
    {
        $filter = ReadingFilter::fromArray(['status' => 'invalid']);
        $this->assertNull($filter->status());
    }

    public function testFromArrayTrimsEmptyCategory(): void
    {
        $filter = ReadingFilter::fromArray(['category' => '  ']);
        $this->assertNull($filter->categorySlug());
    }
}
