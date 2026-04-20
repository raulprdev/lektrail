<?php

namespace LekTrail\Tests\Dashboard;

use LekTrail\Dashboard\ReadingHistory;
use LekTrail\Dashboard\ReadingStats;
use PHPUnit\Framework\TestCase;

class ReadingStatsTest extends TestCase
{
    public function testPercentageCalculation(): void
    {
        $history = new ReadingHistory([
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => [], 'year' => 2025],
            ['post_id' => 2, 'status' => 'read', 'category_slugs' => [], 'year' => 2025],
            ['post_id' => 3, 'status' => 'viewed', 'category_slugs' => [], 'year' => 2025],
        ]);

        $stats = ReadingStats::create($history, 10);

        $this->assertEquals(10, $stats->total());
        $this->assertEquals(2, $stats->read());
        $this->assertEquals(1, $stats->viewed());
        $this->assertEquals(20.0, $stats->percentage());
    }

    public function testZeroTotalReturnsZeroPercentage(): void
    {
        $history = new ReadingHistory([]);
        $stats = ReadingStats::create($history, 0);

        $this->assertEquals(0.0, $stats->percentage());
    }

    public function testFullyReadPercentage(): void
    {
        $history = new ReadingHistory([
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => [], 'year' => 2025],
            ['post_id' => 2, 'status' => 'read', 'category_slugs' => [], 'year' => 2025],
        ]);

        $stats = ReadingStats::create($history, 2);

        $this->assertEquals(100.0, $stats->percentage());
    }
}
