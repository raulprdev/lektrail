<?php

namespace Completionist\Tests;

use Completionist\Settings;
use Completionist\SuggestionsEndpoint;
use Completionist\SuggestionsQuery;
use Completionist\Tests\Mocks\MockPostQuery;
use Completionist\Tests\Mocks\MockJsonResponse;
use PHPUnit\Framework\TestCase;

class SuggestionsEndpointTest extends TestCase {

    private MockPostQuery $posts;
    private MockJsonResponse $response;
    private SuggestionsQuery $query;
    private SuggestionsEndpoint $endpoint;

    protected function setUp(): void {
        $this->posts = new MockPostQuery();
        $this->posts->posts = [
            ['id' => 1, 'title' => 'Post 1', 'url' => '/post-1'],
            ['id' => 2, 'title' => 'Post 2', 'url' => '/post-2'],
            ['id' => 3, 'title' => 'Post 3', 'url' => '/post-3'],
        ];
        $this->response = new MockJsonResponse();
        $this->query = new SuggestionsQuery(new Settings(), $this->posts);
        $this->endpoint = new SuggestionsEndpoint($this->query, $this->response);
        $_GET = [];
    }

    public function testParsesExcludeIds(): void {
        $ids = $this->endpoint->parseExcludeIds('12,45,78');

        $this->assertEquals([12, 45, 78], $ids);
    }

    public function testHandlesEmptyExclude(): void {
        $ids = $this->endpoint->parseExcludeIds('');

        $this->assertEquals([], $ids);
    }

    public function testHandlesInvalidExcludeValues(): void {
        $ids = $this->endpoint->parseExcludeIds('12,abc,45,0,-5');

        $this->assertEquals([12, 45], $ids);
    }

    public function testPassesExcludeIdsToQuery(): void {
        $_GET['exclude'] = '1,2';

        $this->endpoint->handle();

        $this->assertEquals([1, 2], $this->posts->lastQueryArgs['post__not_in']);
    }

    public function testHandleReturnsFilteredPosts(): void {
        $_GET['exclude'] = '1,2';

        $this->endpoint->handle();

        $data = $this->response->successData;
        $this->assertCount(1, $data);
        $this->assertEquals('Post 3', $data[0]['title']);
    }

    public function testHandleReturnsAllPostsWhenNoExclude(): void {
        $this->endpoint->handle();

        $data = $this->response->successData;
        $this->assertCount(3, $data);
    }

    public function testHandleReturnsPostsWithExcerptAndThumbnail(): void {
        $this->posts->posts = [
            [
                'id' => 1,
                'title' => 'Test Post',
                'url' => '/test-post',
                'excerpt' => 'This is a test excerpt.',
                'thumbnail' => 'http://example.com/image.jpg',
            ],
        ];

        $this->endpoint->handle();

        $data = $this->response->successData;
        $this->assertCount(1, $data);
        $this->assertEquals('This is a test excerpt.', $data[0]['excerpt']);
        $this->assertEquals('http://example.com/image.jpg', $data[0]['thumbnail']);
    }
}