<?php

namespace LekTrail\Tests\Renderers;

use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Renderers\ProgressRenderer;
use LekTrail\Tests\Mocks\MockPostCountRepository;
use LekTrail\Tests\Mocks\MockReadingHistoryRepository;
use LekTrail\Tests\Mocks\MockScriptLoader;
use LekTrail\Tests\Mocks\MockUserProvider;
use PHPUnit\Framework\TestCase;

class ProgressRendererTest extends TestCase
{
    private MockUserProvider $users;
    private MockReadingHistoryRepository $history;
    private MockPostCountRepository $counts;
    private MockScriptLoader $scripts;

    protected function setUp(): void
    {
        $this->users = new MockUserProvider();
        $this->history = new MockReadingHistoryRepository();
        $this->counts = new MockPostCountRepository();
        $this->scripts = new MockScriptLoader();
    }

    private function createRenderer(): ProgressRenderer
    {
        return new ProgressRenderer($this->history, $this->counts, $this->users, $this->scripts);
    }

    public function testReturnsEmptyForAnonymousUser(): void
    {
        $this->users->userId = 0;

        $html = $this->createRenderer()->render(new ReadingFilter());

        $this->assertEquals('', $html);
    }

    public function testRendersProgressBar(): void
    {
        $this->users->userId = 42;
        $this->history->records = [
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => [], 'year' => 2025],
            ['post_id' => 2, 'status' => 'read', 'category_slugs' => [], 'year' => 2025],
        ];
        $this->counts->counts['_global'] = 10;

        $html = $this->createRenderer()->render(new ReadingFilter());

        $this->assertStringContainsString('lektrail-progress', $html);
        $this->assertStringContainsString('20%', $html);
        $this->assertStringContainsString('width:20%', $html);
    }

    public function testRendersFilteredProgress(): void
    {
        $this->users->userId = 42;
        $this->history->records = [
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => ['php'], 'year' => 2025],
            ['post_id' => 2, 'status' => 'read', 'category_slugs' => ['javascript'], 'year' => 2025],
        ];
        $this->counts->counts['cat:php'] = 5;

        $html = $this->createRenderer()->render(new ReadingFilter('php'));

        $this->assertStringContainsString('20%', $html);
    }

    public function testRendersZeroProgress(): void
    {
        $this->users->userId = 42;
        $this->history->records = [];
        $this->counts->counts['_global'] = 10;

        $html = $this->createRenderer()->render(new ReadingFilter());

        $this->assertStringContainsString('0%', $html);
        $this->assertStringContainsString('width:0%', $html);
    }

    public function testHandlesZeroTotalPosts(): void
    {
        $this->users->userId = 42;
        $this->history->records = [];
        $this->counts->counts['_global'] = 0;

        $html = $this->createRenderer()->render(new ReadingFilter());

        $this->assertStringContainsString('0%', $html);
    }
}
