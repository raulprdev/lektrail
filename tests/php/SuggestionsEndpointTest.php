<?php

namespace Completionist\Tests;

use Completionist\SuggestionsEndpoint;
use Completionist\Tests\Mocks\MockPostQuery;
use Completionist\Tests\Mocks\MockJsonResponse;
use PHPUnit\Framework\TestCase;

class SuggestionsEndpointTest extends TestCase {

    private MockPostQuery $posts;
    private MockJsonResponse $response;
    private SuggestionsEndpoint $endpoint;

    protected function setUp(): void {
        $this->posts = new MockPostQuery();
        $this->response = new MockJsonResponse();
        $this->endpoint = new SuggestionsEndpoint($this->posts, $this->response);
    }

    public function testParseCountReturnsDefaultWhenNull(): void {
        $count = $this->endpoint->parseCount(null);

        $this->assertEquals(SuggestionsEndpoint::DEFAULT_COUNT, $count);
    }

    public function testParseCountReturnsValueWhenValid(): void {
        $count = $this->endpoint->parseCount('10');

        $this->assertEquals(10, $count);
    }

    public function testParseCountCapsAtMaximum(): void {
        $count = $this->endpoint->parseCount('100');

        $this->assertEquals(SuggestionsEndpoint::MAX_COUNT, $count);
    }

    public function testParseCountReturnsMinimumOne(): void {
        $count = $this->endpoint->parseCount('0');

        $this->assertEquals(1, $count);
    }

    public function testParseCountHandlesNonNumeric(): void {
        $count = $this->endpoint->parseCount('abc');

        $this->assertEquals(SuggestionsEndpoint::DEFAULT_COUNT, $count);
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
        $_GET['count'] = '5';

        $this->endpoint->handle();

        $data = $this->response->successData;
        $this->assertCount(1, $data);
        $this->assertEquals('This is a test excerpt.', $data[0]['excerpt']);
        $this->assertEquals('http://example.com/image.jpg', $data[0]['thumbnail']);
    }
}