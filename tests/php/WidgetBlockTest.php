<?php

namespace Completionist\Tests;

use Completionist\Assets;
use Completionist\Blocks\Widget\WidgetBlock;
use Completionist\Blocks\Widget\WidgetRenderer;
use Completionist\SuggestionsQuery;
use Completionist\Tests\Mocks\MockHooks;
use Completionist\Tests\Mocks\MockPostQuery;
use Completionist\Tests\Mocks\MockScriptLoader;
use Completionist\Tests\Mocks\MockSettingsRepository;
use Completionist\Tests\Mocks\MockTrackingRepository;
use Completionist\Tests\Mocks\MockUserProvider;
use Completionist\TrackingService;
use PHPUnit\Framework\TestCase;

class WidgetBlockTest extends TestCase
{
    private MockHooks $hooks;

    protected function setUp(): void
    {
        $this->hooks = new MockHooks();
    }

    private function createBlock(): WidgetBlock
    {
        $scripts = new MockScriptLoader();
        $settings = new MockSettingsRepository();
        $postQuery = new MockPostQuery();
        $assets = new Assets(
            $scripts,
            dirname(__DIR__, 2) . '/',
            'http://example.com/',
            '1.0.0',
            $settings,
            $postQuery
        );
        $userProvider = new MockUserProvider();
        $trackings = new MockTrackingRepository();
        $trackingService = new TrackingService($userProvider, $trackings, $settings);
        $suggestionsQuery = new SuggestionsQuery($settings, $postQuery);
        $renderer = new WidgetRenderer($assets, $trackingService, $suggestionsQuery, $postQuery);

        return new WidgetBlock($renderer);
    }

    public function testRegistersInitAction(): void
    {
        $block = $this->createBlock();
        $block->register($this->hooks);

        $this->assertArrayHasKey('init', $this->hooks->actions);
    }

    public function testInitActionCallsRegisterBlock(): void
    {
        $block = $this->createBlock();
        $block->register($this->hooks);

        $callback = $this->hooks->actions['init']['callback'];
        $this->assertEquals([$block, 'registerBlock'], $callback);
    }
}