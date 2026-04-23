<?php

namespace LekTrail\Tests\Dashboard;

use LekTrail\Dashboard\PostCountRepository;
use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Tests\Mocks\MockDatabase;
use LekTrail\Tests\Mocks\MockTransients;
use PHPUnit\Framework\TestCase;

class PostCountRepositoryTest extends TestCase
{
    public function testReturnsCachedCount(): void
    {
        $db = new MockDatabase();
        $transients = new MockTransients();
        $transients->store['lektrail_post_counts'] = ['_global' => 42];

        $repo = new PostCountRepository($db, $transients);

        $this->assertEquals(42, $repo->getCount(new ReadingFilter()));
    }

    public function testQueriesOnCacheMiss(): void
    {
        $db = new MockDatabase();
        $db->results = [[['cnt' => 15]]];
        $transients = new MockTransients();

        $repo = new PostCountRepository($db, $transients);
        $count = $repo->getCount(new ReadingFilter());

        $this->assertEquals(15, $count);
    }

    public function testStoresResultInTransient(): void
    {
        $db = new MockDatabase();
        $db->results = [[['cnt' => 15]]];
        $transients = new MockTransients();

        $repo = new PostCountRepository($db, $transients);
        $repo->getCount(new ReadingFilter());

        $cached = $transients->store['lektrail_post_counts'];
        $this->assertEquals(15, $cached['_global']);
    }

    public function testFilterByCategory(): void
    {
        $db = new MockDatabase();
        $db->results = [[['cnt' => 8]]];
        $transients = new MockTransients();

        $repo = new PostCountRepository($db, $transients);
        $count = $repo->getCount(new ReadingFilter('php'));

        $this->assertEquals(8, $count);
        $cached = $transients->store['lektrail_post_counts'];
        $this->assertEquals(8, $cached['cat:php']);
    }

    public function testClearCacheDeletesTransient(): void
    {
        $transients = new MockTransients();
        $transients->store['lektrail_post_counts'] = ['_global' => 42];
        $db = new MockDatabase();

        $repo = new PostCountRepository($db, $transients);
        $repo->clearCache();

        $this->assertFalse($transients->get('lektrail_post_counts'));
    }

    public function testFilterByResolvedCategories(): void
    {
        $db = new MockDatabase();
        $db->results = [[['cnt' => 12]]];
        $transients = new MockTransients();

        $filter = new ReadingFilter('programming');
        $resolved = $filter->withResolvedCategories(['programming', 'php', 'javascript']);

        $repo = new PostCountRepository($db, $transients);
        $count = $repo->getCount($resolved);

        $this->assertEquals(12, $count);
        $sql = $db->queries[0]['sql'];
        $this->assertStringContainsString('IN', $sql);
    }
}
