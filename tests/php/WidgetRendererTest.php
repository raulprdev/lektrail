<?php

namespace Completionist\Tests;

use Completionist\Assets;
use Completionist\Blocks\Widget\WidgetRenderer;
use Completionist\SuggestionsQuery;
use Completionist\Tests\Mocks\MockPostQuery;
use Completionist\Tests\Mocks\MockScriptLoader;
use Completionist\Tests\Mocks\MockPluginConfigRepository;
use Completionist\Tests\Mocks\MockTrackingRepository;
use Completionist\Tests\Mocks\MockUserProvider;
use Completionist\TrackingService;
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

        $this->assertStringContainsString('id="completionist-widget"', $html);
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
        $this->assertStringContainsString('CompletionistInlineData', $inlineCode);
    }

    public function testDoesNotInjectInlineDataWhenClientSide(): void
    {
        $this->pluginConfigs = new MockPluginConfigRepository([ 'track_logged_in_users' => false]);

        $renderer = $this->createRenderer();
        $renderer->render();

        $inlineCode = $this->scripts->inlineScripts[Assets::HANDLE_WIDGET]['code'];
        $this->assertStringNotContainsString('CompletionistInlineData', $inlineCode);
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
}
