<?php

namespace LekTrail\Tests;

use LekTrail\Assets;
use LekTrail\Shortcodes\WidgetShortcode;
use LekTrail\SuggestionsQuery;
use LekTrail\Tests\Mocks\MockPluginConfigRepository;
use LekTrail\Tests\Mocks\MockPostQuery;
use LekTrail\Tests\Mocks\MockScriptLoader;
use LekTrail\Tests\Mocks\MockTrackingRepository;
use LekTrail\Tests\Mocks\MockUserProvider;
use LekTrail\TrackingService;
use LekTrail\WidgetRenderer;
use PHPUnit\Framework\TestCase;

class WidgetShortcodeTest extends TestCase
{
    private MockScriptLoader $scripts;
    private MockPluginConfigRepository $pluginConfigs;
    private MockUserProvider $userProvider;
    private MockTrackingRepository $trackings;
    private MockPostQuery $postQuery;

    protected function setUp(): void
    {
        $this->scripts       = new MockScriptLoader();
        $this->pluginConfigs = new MockPluginConfigRepository();
        $this->userProvider  = new MockUserProvider();
        $this->trackings = new MockTrackingRepository();
        $this->postQuery = new MockPostQuery();
    }

    private function createWidgetShortcode(): WidgetShortcode
    {
        $assets = new Assets(
            $this->scripts,
            dirname(__DIR__, 2) . '/',
            'http://example.com/',
            '1.0.0',
            $this->pluginConfigs,
            $this->postQuery
        );
        $trackingService = new TrackingService($this->userProvider, $this->trackings, $this->pluginConfigs);
        $suggestionsQuery = new SuggestionsQuery($this->pluginConfigs, $this->postQuery);
        $renderer = new WidgetRenderer($assets, $trackingService, $suggestionsQuery, $this->postQuery, $this->pluginConfigs);

        return new WidgetShortcode($renderer);
    }

    public function testInjectsInlineDataWhenServerSideEnabled(): void
    {
        $this->pluginConfigs        = new MockPluginConfigRepository([ 'track_logged_in_users' => true]);
        $this->userProvider->userId = 42;

        $shortcode = $this->createWidgetShortcode();
        $shortcode->render([]);

        $inlineCode = $this->scripts->inlineScripts[Assets::HANDLE_WIDGET]['code'];
        $this->assertStringContainsString('LekTrailInlineData', $inlineCode);
    }

    public function testDoesNotInjectInlineDataWhenServerSideDisabled(): void
    {
        $this->pluginConfigs = new MockPluginConfigRepository([ 'track_logged_in_users' => false]);

        $shortcode = $this->createWidgetShortcode();
        $shortcode->render([]);

        $inlineCode = $this->scripts->inlineScripts[Assets::HANDLE_WIDGET]['code'];
        $this->assertStringNotContainsString('LekTrailInlineData', $inlineCode);
    }

    public function testInlineDataContainsFullPostData(): void
    {
        $this->pluginConfigs        = new MockPluginConfigRepository([ 'track_logged_in_users' => true]);
        $this->userProvider->userId = 42;
        $this->trackings->track(42, 123, 'viewed');
        $this->postQuery->postData[123] = [
            'id' => 123,
            'title' => 'Test Post Title',
            'url' => '/test-post/',
            'excerpt' => 'Test excerpt',
            'thumbnail' => 'http://example.com/image.jpg',
        ];

        $shortcode = $this->createWidgetShortcode();
        $shortcode->render([]);

        $inlineCode = $this->scripts->inlineScripts[Assets::HANDLE_WIDGET]['code'];
        $this->assertStringContainsString('Test Post Title', $inlineCode);
        $this->assertStringContainsString('test-post', $inlineCode);
    }
}
