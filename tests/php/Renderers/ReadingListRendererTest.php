<?php

namespace LekTrail\Tests\Renderers;

use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Renderers\ReadingListRenderer;
use LekTrail\Tests\Mocks\MockPostQuery;
use LekTrail\Tests\Mocks\MockReadingHistoryRepository;
use LekTrail\Tests\Mocks\MockScriptLoader;
use LekTrail\Tests\Mocks\MockUserProvider;
use PHPUnit\Framework\TestCase;

class ReadingListRendererTest extends TestCase
{
    private MockUserProvider $users;
    private MockReadingHistoryRepository $history;
    private MockPostQuery $postQuery;
    private MockScriptLoader $scripts;

    protected function setUp(): void
    {
        $this->users = new MockUserProvider();
        $this->history = new MockReadingHistoryRepository();
        $this->postQuery = new MockPostQuery();
        $this->scripts = new MockScriptLoader();
    }

    private function createRenderer(): ReadingListRenderer
    {
        return new ReadingListRenderer($this->history, $this->postQuery, $this->users, $this->scripts);
    }

    public function testReturnsEmptyForAnonymousUser(): void
    {
        $this->users->userId = 0;

        $html = $this->createRenderer()->render(new ReadingFilter());

        $this->assertEquals('', $html);
    }

    public function testRendersPostList(): void
    {
        $this->users->userId = 42;
        $this->history->records = [
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => ['php'], 'year' => 2025],
            ['post_id' => 2, 'status' => 'viewed', 'category_slugs' => ['php'], 'year' => 2025],
        ];
        $this->postQuery->postData = [
            1 => ['id' => 1, 'title' => 'PHP Basics', 'url' => '/php-basics'],
            2 => ['id' => 2, 'title' => 'PHP Advanced', 'url' => '/php-advanced'],
        ];

        $html = $this->createRenderer()->render(new ReadingFilter());

        $this->assertStringContainsString('lektrail-reading-list', $html);
        $this->assertStringContainsString('PHP Basics', $html);
        $this->assertStringContainsString('/php-basics', $html);
        $this->assertStringContainsString('PHP Advanced', $html);
    }

    public function testRendersFilteredList(): void
    {
        $this->users->userId = 42;
        $this->history->records = [
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => ['php'], 'year' => 2025],
            ['post_id' => 2, 'status' => 'read', 'category_slugs' => ['javascript'], 'year' => 2025],
        ];
        $this->postQuery->postData = [
            1 => ['id' => 1, 'title' => 'PHP Basics', 'url' => '/php-basics'],
        ];

        $html = $this->createRenderer()->render(new ReadingFilter('php'));

        $this->assertStringContainsString('PHP Basics', $html);
        $this->assertStringNotContainsString('javascript', $html);
    }

    public function testRendersEmptyList(): void
    {
        $this->users->userId = 42;
        $this->history->records = [];

        $html = $this->createRenderer()->render(new ReadingFilter());

        $this->assertStringContainsString('lektrail-reading-list', $html);
    }
}
