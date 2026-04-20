<?php

namespace LekTrail\Tests\Dashboard;

use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Dashboard\ReadingHistory;
use PHPUnit\Framework\TestCase;

class ReadingHistoryTest extends TestCase
{
    private function sampleRecords(): array
    {
        return [
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => ['php', 'tutorials'], 'year' => 2025],
            ['post_id' => 2, 'status' => 'viewed', 'category_slugs' => ['php'], 'year' => 2024],
            ['post_id' => 3, 'status' => 'read', 'category_slugs' => ['javascript'], 'year' => 2025],
            ['post_id' => 4, 'status' => 'viewed', 'category_slugs' => ['php'], 'year' => 2025],
        ];
    }

    public function testReadCount(): void
    {
        $history = new ReadingHistory($this->sampleRecords());
        $this->assertEquals(2, $history->readCount());
    }

    public function testViewedCount(): void
    {
        $history = new ReadingHistory($this->sampleRecords());
        $this->assertEquals(2, $history->viewedCount());
    }

    public function testPostIds(): void
    {
        $history = new ReadingHistory($this->sampleRecords());
        $this->assertEquals([1, 2, 3, 4], $history->postIds());
    }

    public function testFilterByCategory(): void
    {
        $history = new ReadingHistory($this->sampleRecords());
        $filtered = $history->filter(new ReadingFilter('php'));

        $this->assertEquals([1, 2, 4], $filtered->postIds());
    }

    public function testFilterByYear(): void
    {
        $history = new ReadingHistory($this->sampleRecords());
        $filtered = $history->filter(new ReadingFilter(null, 2025));

        $this->assertEquals([1, 3, 4], $filtered->postIds());
    }

    public function testFilterByStatus(): void
    {
        $history = new ReadingHistory($this->sampleRecords());
        $filtered = $history->filter(new ReadingFilter(null, null, 'read'));

        $this->assertEquals([1, 3], $filtered->postIds());
    }

    public function testFilterByCategoryAndYear(): void
    {
        $history = new ReadingHistory($this->sampleRecords());
        $filtered = $history->filter(new ReadingFilter('php', 2025));

        $this->assertEquals([1, 4], $filtered->postIds());
    }

    public function testFilterByCategoryYearAndStatus(): void
    {
        $history = new ReadingHistory($this->sampleRecords());
        $filtered = $history->filter(new ReadingFilter('php', 2025, 'read'));

        $this->assertEquals([1], $filtered->postIds());
    }

    public function testNoFilterReturnsAll(): void
    {
        $history = new ReadingHistory($this->sampleRecords());
        $filtered = $history->filter(new ReadingFilter());

        $this->assertEquals([1, 2, 3, 4], $filtered->postIds());
    }

    public function testEmptyHistory(): void
    {
        $history = new ReadingHistory([]);

        $this->assertEquals(0, $history->readCount());
        $this->assertEquals(0, $history->viewedCount());
        $this->assertEquals([], $history->postIds());
    }
}
