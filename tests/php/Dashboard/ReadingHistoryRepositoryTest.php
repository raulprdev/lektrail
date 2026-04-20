<?php

namespace LekTrail\Tests\Dashboard;

use LekTrail\Dashboard\ReadingHistoryRepository;
use LekTrail\Tests\Mocks\MockDatabase;
use PHPUnit\Framework\TestCase;

class ReadingHistoryRepositoryTest extends TestCase
{
    public function testReturnsFormattedRecords(): void
    {
        $db = new MockDatabase();
        $db->results = [[
            (object) ['post_id' => 1, 'status' => 'read', 'year' => 2025, 'slugs' => 'php,tutorials'],
            (object) ['post_id' => 2, 'status' => 'viewed', 'year' => 2024, 'slugs' => 'javascript'],
        ]];

        $repo = new ReadingHistoryRepository($db);
        $records = $repo->getAllForUser(42);

        $this->assertCount(2, $records);
        $this->assertEquals(1, $records[0]['post_id']);
        $this->assertEquals('read', $records[0]['status']);
        $this->assertEquals(2025, $records[0]['year']);
        $this->assertEquals(['php', 'tutorials'], $records[0]['category_slugs']);
    }

    public function testHandlesPostsWithNoCategories(): void
    {
        $db = new MockDatabase();
        $db->results = [[
            (object) ['post_id' => 1, 'status' => 'read', 'year' => 2025, 'slugs' => null],
        ]];

        $repo = new ReadingHistoryRepository($db);
        $records = $repo->getAllForUser(42);

        $this->assertEquals([], $records[0]['category_slugs']);
    }

    public function testCachesPerRequest(): void
    {
        $db = new MockDatabase();
        $db->results = [[
            (object) ['post_id' => 1, 'status' => 'read', 'year' => 2025, 'slugs' => 'php'],
        ]];

        $repo = new ReadingHistoryRepository($db);
        $repo->getAllForUser(42);

        $db->results = [[]];
        $second = $repo->getAllForUser(42);

        $this->assertCount(1, $second);
    }

    public function testReturnsEmptyForNoHistory(): void
    {
        $db = new MockDatabase();
        $db->results = [[]];

        $repo = new ReadingHistoryRepository($db);
        $records = $repo->getAllForUser(42);

        $this->assertEquals([], $records);
    }
}
