<?php

namespace LekTrail\Tests;

use LekTrail\Assets;
use LekTrail\Blocks\Widget\WidgetBlock;
use LekTrail\SuggestionsQuery;
use LekTrail\Tests\Mocks\MockHooks;
use LekTrail\Tests\Mocks\MockPluginConfigRepository;
use LekTrail\Tests\Mocks\MockPostQuery;
use LekTrail\Tests\Mocks\MockScriptLoader;
use LekTrail\Tests\Mocks\MockTrackingRepository;
use LekTrail\Tests\Mocks\MockUserProvider;
use LekTrail\TrackingService;
use LekTrail\WidgetRenderer;
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
        $pluginConfigs = new MockPluginConfigRepository();
        $postQuery = new MockPostQuery();
        $assets = new Assets(
            $scripts,
            dirname(__DIR__, 2) . '/',
            'http://example.com/',
            '1.0.0',
            $pluginConfigs,
            $postQuery
        );
        $userProvider = new MockUserProvider();
        $trackings = new MockTrackingRepository();
        $trackingService = new TrackingService($userProvider, $trackings, $pluginConfigs);
        $suggestionsQuery = new SuggestionsQuery($pluginConfigs, $postQuery);
        $renderer = new WidgetRenderer($assets, $trackingService, $suggestionsQuery, $postQuery, $pluginConfigs);

        return new WidgetBlock($renderer, $assets, dirname(__DIR__, 2) . '/');
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
