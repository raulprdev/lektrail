<?php

namespace Completionist\Tests;

use Completionist\Assets;
use Completionist\Blocks\Widget\WidgetRenderer;
use Completionist\SuggestionsQuery;
use Completionist\Tests\Mocks\MockPostQuery;
use Completionist\Tests\Mocks\MockScriptLoader;
use Completionist\Tests\Mocks\MockSettingsRepository;
use Completionist\Tests\Mocks\MockTrackingRepository;
use Completionist\Tests\Mocks\MockUserProvider;
use Completionist\TrackingService;
use PHPUnit\Framework\TestCase;

class WidgetRendererTest extends TestCase
{
    private MockScriptLoader $scripts;
    private MockSettingsRepository $settings;
    private MockUserProvider $userProvider;
    private MockTrackingRepository $trackings;
    private MockPostQuery $postQuery;

    protected function setUp(): void
    {
        $this->scripts = new MockScriptLoader();
        $this->settings = new MockSettingsRepository();
        $this->userProvider = new MockUserProvider();
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
            $this->settings,
            $this->postQuery
        );
        $trackingService = new TrackingService($this->userProvider, $this->trackings, $this->settings);
        $suggestionsQuery = new SuggestionsQuery($this->settings, $this->postQuery);

        return new WidgetRenderer($assets, $trackingService, $suggestionsQuery, $this->postQuery);
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
        $this->settings = new MockSettingsRepository(['track_logged_in_users' => true]);
        $this->userProvider->userId = 42;

        $renderer = $this->createRenderer();
        $renderer->render();

        $inlineCode = $this->scripts->inlineScripts[Assets::HANDLE_WIDGET]['code'];
        $this->assertStringContainsString('CompletionistInlineData', $inlineCode);
    }

    public function testDoesNotInjectInlineDataWhenClientSide(): void
    {
        $this->settings = new MockSettingsRepository(['track_logged_in_users' => false]);

        $renderer = $this->createRenderer();
        $renderer->render();

        $inlineCode = $this->scripts->inlineScripts[Assets::HANDLE_WIDGET]['code'];
        $this->assertStringNotContainsString('CompletionistInlineData', $inlineCode);
    }

    public function testInlineDataContainsEnrichedPostData(): void
    {
        $this->settings = new MockSettingsRepository(['track_logged_in_users' => true]);
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