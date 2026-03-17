<?php

namespace Completionist\Tests;

use Completionist\PreviewEndpoint;
use Completionist\Tests\Mocks\MockPostQuery;
use PHPUnit\Framework\TestCase;

class PreviewEndpointTest extends TestCase
{
    private MockPostQuery $postQuery;
    private PreviewEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->postQuery = new MockPostQuery();
        $this->endpoint  = new PreviewEndpoint($this->postQuery);
        $_GET = [];
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }

    public function testReturnsViewedReadAndSuggestions(): void
    {
        $this->postQuery->posts = $this->createPosts(10);

        $result = $this->endpoint->handle();

        $this->assertArrayHasKey('viewed', $result);
        $this->assertArrayHasKey('read', $result);
        $this->assertArrayHasKey('suggestions', $result);
    }

    public function testUsesDefaultValuesWhenNoParams(): void
    {
        $this->postQuery->posts = $this->createPosts(20);

        $result = $this->endpoint->handle();

        $this->assertCount(3, $result['viewed']);
        $this->assertCount(3, $result['read']);
        $this->assertCount(3, $result['suggestions']);
    }

    public function testRespectsMaxViewedParam(): void
    {
        $_GET['maxViewed'] = '5';
        $this->postQuery->posts = $this->createPosts(20);

        $result = $this->endpoint->handle();

        $this->assertCount(5, $result['viewed']);
    }

    public function testRespectsMaxReadParam(): void
    {
        $_GET['maxRead'] = '7';
        $this->postQuery->posts = $this->createPosts(20);

        $result = $this->endpoint->handle();

        $this->assertCount(7, $result['read']);
    }

    public function testRespectsMaxSuggestionsParam(): void
    {
        $_GET['maxSuggestions'] = '4';
        $this->postQuery->posts = $this->createPosts(20);

        $result = $this->endpoint->handle();

        $this->assertCount(4, $result['suggestions']);
    }

    public function testFetchesExactAmountNeeded(): void
    {
        $_GET['maxViewed'] = '5';
        $_GET['maxRead'] = '3';
        $_GET['maxSuggestions'] = '2';
        $this->postQuery->posts = $this->createPosts(20);

        $this->endpoint->handle();

        $this->assertEquals(10, $this->postQuery->lastRecentLimit);
    }

    public function testHandlesFewerPostsThanRequested(): void
    {
        $_GET['maxViewed'] = '10';
        $_GET['maxRead'] = '10';
        $_GET['maxSuggestions'] = '10';
        $this->postQuery->posts = $this->createPosts(15);

        $result = $this->endpoint->handle();

        $this->assertCount(10, $result['viewed']);
        $this->assertCount(5, $result['read']);
        $this->assertCount(0, $result['suggestions']);
    }

    private function createPosts(int $count): array
    {
        $posts = [];
        for ($i = 1; $i <= $count; $i++) {
            $posts[] = [
                'id' => $i,
                'title' => "Post $i",
                'url' => "/post-$i",
            ];
        }
        return $posts;
    }
}
