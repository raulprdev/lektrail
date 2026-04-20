<?php

namespace LekTrail\Tests\Shortcodes;

use LekTrail\Renderers\ReadingListRenderer;
use LekTrail\Shortcodes\ReadingListShortcode;
use LekTrail\Tests\Mocks\MockHooks;
use LekTrail\Tests\Mocks\MockPostQuery;
use LekTrail\Tests\Mocks\MockReadingHistoryRepository;
use LekTrail\Tests\Mocks\MockScriptLoader;
use LekTrail\Tests\Mocks\MockUserProvider;
use PHPUnit\Framework\TestCase;

class ReadingListShortcodeTest extends TestCase
{
    private MockUserProvider $users;
    private MockReadingHistoryRepository $history;
    private MockPostQuery $postQuery;

    protected function setUp(): void
    {
        $this->users = new MockUserProvider();
        $this->users->userId = 42;
        $this->history = new MockReadingHistoryRepository();
        $this->postQuery = new MockPostQuery();
    }

    private function createShortcode(): ReadingListShortcode
    {
        $renderer = new ReadingListRenderer(
            $this->history,
            $this->postQuery,
            $this->users,
            new MockScriptLoader()
        );
        return new ReadingListShortcode($renderer);
    }

    public function testRegistersShortcode(): void
    {
        $hooks = new MockHooks();
        $this->createShortcode()->register($hooks);

        $this->assertArrayHasKey('lektrail-list', $hooks->shortcodes);
    }

    public function testRendersWithNoAttributes(): void
    {
        $this->history->records = [
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => [], 'year' => 2025],
        ];
        $this->postQuery->postData = [
            1 => ['id' => 1, 'title' => 'Test Post', 'url' => '/test'],
        ];

        $html = $this->createShortcode()->render([]);

        $this->assertStringContainsString('lektrail-reading-list', $html);
        $this->assertStringContainsString('Test Post', $html);
    }

    public function testPassesCategoryFilter(): void
    {
        $this->history->records = [
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => ['php'], 'year' => 2025],
            ['post_id' => 2, 'status' => 'read', 'category_slugs' => ['javascript'], 'year' => 2025],
        ];
        $this->postQuery->postData = [
            1 => ['id' => 1, 'title' => 'PHP Post', 'url' => '/php'],
        ];

        $html = $this->createShortcode()->render(['category' => 'php']);

        $this->assertStringContainsString('PHP Post', $html);
    }
}
