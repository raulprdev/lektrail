<?php

namespace LekTrail\Tests\Dashboard;

use LekTrail\Dashboard\ProgressCalculator;
use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Dashboard\ReadingHistory;
use LekTrail\Tests\Mocks\MockPostCountRepository;
use PHPUnit\Framework\TestCase;

class ProgressCalculatorTest extends TestCase
{
    public function testCalculatesGlobalProgress(): void
    {
        $history = new ReadingHistory([
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => ['php'], 'year' => 2025],
            ['post_id' => 2, 'status' => 'viewed', 'category_slugs' => ['php'], 'year' => 2025],
        ]);

        $counts = new MockPostCountRepository();
        $counts->counts['_global'] = 10;

        $calculator = new ProgressCalculator();
        $stats = $calculator->calculate($history, $counts, new ReadingFilter());

        $this->assertEquals(10, $stats->total());
        $this->assertEquals(1, $stats->read());
        $this->assertEquals(1, $stats->viewed());
        $this->assertEquals(10.0, $stats->percentage());
    }

    public function testCalculatesFilteredProgress(): void
    {
        $history = new ReadingHistory([
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => ['php'], 'year' => 2025],
            ['post_id' => 2, 'status' => 'read', 'category_slugs' => ['javascript'], 'year' => 2025],
        ]);

        $counts = new MockPostCountRepository();
        $counts->counts['cat:php'] = 5;

        $filter = new ReadingFilter('php');
        $calculator = new ProgressCalculator();
        $stats = $calculator->calculate($history, $counts, $filter);

        $this->assertEquals(5, $stats->total());
        $this->assertEquals(1, $stats->read());
        $this->assertEquals(20.0, $stats->percentage());
    }

    public function testCalculatesCategoryAndYearProgress(): void
    {
        $history = new ReadingHistory([
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => ['php'], 'year' => 2025],
            ['post_id' => 2, 'status' => 'read', 'category_slugs' => ['php'], 'year' => 2024],
        ]);

        $counts = new MockPostCountRepository();
        $counts->counts['cat:php|year:2025'] = 4;

        $filter = new ReadingFilter('php', 2025);
        $calculator = new ProgressCalculator();
        $stats = $calculator->calculate($history, $counts, $filter);

        $this->assertEquals(4, $stats->total());
        $this->assertEquals(1, $stats->read());
        $this->assertEquals(25.0, $stats->percentage());
    }
}
