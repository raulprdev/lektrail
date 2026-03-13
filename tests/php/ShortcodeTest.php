<?php

namespace Completionist\Tests;

use Completionist\Assets;
use Completionist\Shortcode;
use Completionist\SuggestionsQuery;
use Completionist\Tests\Mocks\MockPostQuery;
use Completionist\Tests\Mocks\MockScriptLoader;
use Completionist\Tests\Mocks\MockSettingsRepository;
use Completionist\Tests\Mocks\MockTrackingRepository;
use Completionist\Tests\Mocks\MockUserProvider;
use Completionist\TrackingService;
use PHPUnit\Framework\TestCase;

class ShortcodeTest extends TestCase
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

    private function createShortcode(): Shortcode
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

        return new Shortcode($assets, $trackingService, $suggestionsQuery, $this->postQuery);
    }

    public function testInjectsInlineDataWhenServerSideEnabled(): void
    {
        $this->settings             = new MockSettingsRepository(['track_logged_in_users' => true]);
        $this->userProvider->userId = 42;

        $shortcode = $this->createShortcode();
        $shortcode->render([]);

        $inlineCode = $this->scripts->inlineScripts[Assets::HANDLE_WIDGET]['code'];
        $this->assertStringContainsString('CompletionistInlineData', $inlineCode);
    }

    public function testDoesNotInjectInlineDataWhenServerSideDisabled(): void
    {
        $this->settings = new MockSettingsRepository(['track_logged_in_users' => false]);

        $shortcode = $this->createShortcode();
        $shortcode->render([]);

        $inlineCode = $this->scripts->inlineScripts[Assets::HANDLE_WIDGET]['code'];
        $this->assertStringNotContainsString('CompletionistInlineData', $inlineCode);
    }

    public function testInlineDataContainsFullPostData(): void
    {
        $this->settings             = new MockSettingsRepository(['track_logged_in_users' => true]);
        $this->userProvider->userId = 42;
        $this->trackings->track(42, 123, 'viewed');
        $this->postQuery->postData[123] = [
            'id' => 123,
            'title' => 'Test Post Title',
            'url' => '/test-post/',
            'excerpt' => 'Test excerpt',
            'thumbnail' => 'http://example.com/image.jpg',
        ];

        $shortcode = $this->createShortcode();
        $shortcode->render([]);

        $inlineCode = $this->scripts->inlineScripts[Assets::HANDLE_WIDGET]['code'];
        $this->assertStringContainsString('Test Post Title', $inlineCode);
        $this->assertStringContainsString('test-post', $inlineCode);
    }
}
