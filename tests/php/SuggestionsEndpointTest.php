<?php

namespace LekTrail\Tests;

use LekTrail\SuggestionsEndpoint;
use LekTrail\SuggestionsQuery;
use LekTrail\Tests\Mocks\MockJsonResponse;
use LekTrail\Tests\Mocks\MockPluginConfigRepository;
use LekTrail\Tests\Mocks\MockPostQuery;
use PHPUnit\Framework\TestCase;

class SuggestionsEndpointTest extends TestCase
{
    private MockPostQuery $postQuery;
    private MockJsonResponse $jsonResponse;
    private SuggestionsQuery $suggestionsQuery;
    private SuggestionsEndpoint $suggestionsEndpoint;

    protected function setUp(): void
    {
        $this->postQuery        = new MockPostQuery();
        $this->postQuery->posts = [
            ['id' => 1, 'title' => 'Post 1', 'url' => '/post-1'],
            ['id' => 2, 'title' => 'Post 2', 'url' => '/post-2'],
            ['id' => 3, 'title' => 'Post 3', 'url' => '/post-3'],
        ];
        $this->jsonResponse         = new MockJsonResponse();
        $this->suggestionsQuery            = new SuggestionsQuery(new MockPluginConfigRepository(), $this->postQuery);
        $this->suggestionsEndpoint         = new SuggestionsEndpoint($this->suggestionsQuery, $this->jsonResponse);
        $_GET = [];
    }

    public function testParsesExcludeIds(): void
    {
        $ids = $this->suggestionsEndpoint->parseExcludeIds('12,45,78');

        $this->assertEquals([12, 45, 78], $ids);
    }

    public function testHandlesEmptyExclude(): void
    {
        $ids = $this->suggestionsEndpoint->parseExcludeIds('');

        $this->assertEquals([], $ids);
    }

    public function testHandlesInvalidExcludeValues(): void
    {
        $ids = $this->suggestionsEndpoint->parseExcludeIds('12,abc,45,0,-5');

        $this->assertEquals([12, 45], $ids);
    }

    public function testPassesExcludeIdsToQuery(): void
    {
        $_GET['exclude'] = '1,2';

        $this->suggestionsEndpoint->handle();

        $this->assertEquals([1, 2], $this->postQuery->lastQueryArgs['post__not_in']);
    }

    public function testHandleReturnsFilteredPosts(): void
    {
        $_GET['exclude'] = '1,2';

        $this->suggestionsEndpoint->handle();

        $data = $this->jsonResponse->successData;
        $this->assertCount(1, $data);
        $this->assertEquals('Post 3', $data[0]['title']);
    }

    public function testHandleReturnsAllPostsWhenNoExclude(): void
    {
        $this->suggestionsEndpoint->handle();

        $data = $this->jsonResponse->successData;
        $this->assertCount(3, $data);
    }

    public function testHandleReturnsPostsWithExcerptAndThumbnail(): void
    {
        $this->postQuery->posts = [
            [
                'id' => 1,
                'title' => 'Test Post',
                'url' => '/test-post',
                'excerpt' => 'This is a test excerpt.',
                'thumbnail' => 'http://example.com/image.jpg',
            ],
        ];

        $this->suggestionsEndpoint->handle();

        $data = $this->jsonResponse->successData;
        $this->assertCount(1, $data);
        $this->assertEquals('This is a test excerpt.', $data[0]['excerpt']);
        $this->assertEquals('http://example.com/image.jpg', $data[0]['thumbnail']);
    }
}
