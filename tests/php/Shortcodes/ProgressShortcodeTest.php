<?php

namespace LekTrail\Tests\Shortcodes;

use LekTrail\Renderers\ProgressRenderer;
use LekTrail\Shortcodes\ProgressShortcode;
use LekTrail\Tests\Mocks\MockHooks;
use LekTrail\Tests\Mocks\MockPostCountRepository;
use LekTrail\Tests\Mocks\MockReadingHistoryRepository;
use LekTrail\Tests\Mocks\MockScriptLoader;
use LekTrail\Tests\Mocks\MockUserProvider;
use PHPUnit\Framework\TestCase;

class ProgressShortcodeTest extends TestCase
{
    private MockUserProvider $users;
    private MockReadingHistoryRepository $history;
    private MockPostCountRepository $counts;

    protected function setUp(): void
    {
        $this->users = new MockUserProvider();
        $this->users->userId = 42;
        $this->history = new MockReadingHistoryRepository();
        $this->counts = new MockPostCountRepository();
    }

    private function createShortcode(): ProgressShortcode
    {
        $renderer = new ProgressRenderer(
            $this->history,
            $this->counts,
            $this->users,
            new MockScriptLoader()
        );
        return new ProgressShortcode($renderer);
    }

    public function testRegistersShortcode(): void
    {
        $hooks = new MockHooks();
        $this->createShortcode()->register($hooks);

        $this->assertArrayHasKey('lektrail-progress', $hooks->shortcodes);
    }

    public function testRendersWithNoAttributes(): void
    {
        $this->counts->counts['_global'] = 10;
        $this->history->records = [
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => [], 'year' => 2025],
        ];

        $html = $this->createShortcode()->render([]);

        $this->assertStringContainsString('lektrail-progress', $html);
    }

    public function testPassesCategoryFilter(): void
    {
        $this->counts->counts['cat:php'] = 5;
        $this->history->records = [
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => ['php'], 'year' => 2025],
        ];

        $html = $this->createShortcode()->render(['category' => 'php']);

        $this->assertStringContainsString('20%', $html);
    }

    public function testPassesYearFilter(): void
    {
        $this->counts->counts['year:2025'] = 10;
        $this->history->records = [
            ['post_id' => 1, 'status' => 'read', 'category_slugs' => [], 'year' => 2025],
            ['post_id' => 2, 'status' => 'read', 'category_slugs' => [], 'year' => 2024],
        ];

        $html = $this->createShortcode()->render(['year' => '2025']);

        $this->assertStringContainsString('10%', $html);
    }
}
