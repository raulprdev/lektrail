<?php

namespace LekTrail\Tests;

use LekTrail\Assets;
use LekTrail\SuggestionsQuery;
use LekTrail\Tests\Mocks\MockPluginConfigRepository;
use LekTrail\Tests\Mocks\MockPostQuery;
use LekTrail\Tests\Mocks\MockScriptLoader;
use LekTrail\Tests\Mocks\MockTrackingRepository;
use LekTrail\Tests\Mocks\MockUserProvider;
use LekTrail\TrackingService;
use LekTrail\WidgetRenderer;
use PHPUnit\Framework\TestCase;

class WidgetRendererTest extends TestCase
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

    private function createRenderer(): WidgetRenderer
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

        return new WidgetRenderer($assets, $trackingService, $suggestionsQuery, $this->postQuery, $this->pluginConfigs);
    }

    public function testRendersContainerWithCorrectId(): void
    {
        $renderer = $this->createRenderer();
        $html = $renderer->render();

        $this->assertStringContainsString('id="lektrail-widget"', $html);
    }

    public function testRendersContainerWithDataEndpoints(): void
    {
        $renderer = $this->createRenderer();
        $html = $renderer->render();

        $this->assertStringContainsString('data-endpoint=', $html);
        $this->assertStringContainsString('data-posts-endpoint=', $html);
    }

    public function testEnqueuesWidgetAssets(): void
    {
        $renderer = $this->createRenderer();
        $renderer->render();

        $this->assertArrayHasKey(Assets::HANDLE_WIDGET, $this->scripts->scripts);
        $this->assertArrayHasKey(Assets::HANDLE_WIDGET, $this->scripts->styles);
    }

    public function testInjectsInlineDataForServerSideTracking(): void
    {
        $this->pluginConfigs        = new MockPluginConfigRepository([ 'track_logged_in_users' => true]);
        $this->userProvider->userId = 42;

        $renderer = $this->createRenderer();
        $renderer->render();

        $inlineCode = $this->scripts->inlineScripts[Assets::HANDLE_WIDGET]['code'];
        $this->assertStringContainsString('LekTrailInlineData', $inlineCode);
    }

    public function testDoesNotInjectInlineDataWhenClientSide(): void
    {
        $this->pluginConfigs = new MockPluginConfigRepository([ 'track_logged_in_users' => false]);

        $renderer = $this->createRenderer();
        $renderer->render();

        $inlineCode = $this->scripts->inlineScripts[Assets::HANDLE_WIDGET]['code'];
        $this->assertStringNotContainsString('LekTrailInlineData', $inlineCode);
    }

    public function testInlineDataContainsEnrichedPostData(): void
    {
        $this->pluginConfigs        = new MockPluginConfigRepository([ 'track_logged_in_users' => true]);
        $this->userProvider->userId = 42;
        $this->trackings->track(42, 123, 'viewed');
        $this->postQuery->postData[123] = [
            'id' => 123,
            'title' => 'Test Post',
            'url' => '/test-post/',
            'excerpt' => 'Test excerpt',
            'thumbnail' => 'http://example.com/image.jpg',
        ];

        $renderer = $this->createRenderer();
        $renderer->render();

        $inlineCode = $this->scripts->inlineScripts[Assets::HANDLE_WIDGET]['code'];
        $this->assertStringContainsString('Test Post', $inlineCode);
    }

    public function testIncludesWrapperAttributesWhenProvided(): void
    {
        $renderer = $this->createRenderer();
        $html = $renderer->render([], 'class="wp-block-lektrail-widget has-background"');

        $this->assertStringContainsString('class="wp-block-lektrail-widget has-background"', $html);
        $this->assertStringContainsString('id="lektrail-widget"', $html);
    }

    public function testUsesDefaultClassWhenNoWrapperAttributes(): void
    {
        $renderer = $this->createRenderer();
        $html = $renderer->render();

        $this->assertStringContainsString('class="lektrail-widget"', $html);
    }
}
