<?php

namespace Completionist\Tests;

use Completionist\AdminPage;
use Completionist\Assets;
use Completionist\Plugin;
use Completionist\Shortcodes\WidgetShortcode;
use Completionist\SuggestionsEndpoint;
use Completionist\SuggestionsQuery;
use Completionist\Tests\Mocks\MockContext;
use Completionist\Tests\Mocks\MockHooks;
use Completionist\Tests\Mocks\MockJsonResponse;
use Completionist\Tests\Mocks\MockPostQuery;
use Completionist\Tests\Mocks\MockScriptLoader;
use Completionist\Tests\Mocks\MockSettingsRepository;
use Completionist\Tests\Mocks\MockTrackingRepository;
use Completionist\Tests\Mocks\MockUserProvider;
use Completionist\TrackingEndpoint;
use Completionist\TrackingService;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    private MockScriptLoader $scripts;
    private MockSettingsRepository $settings;
    private MockContext $context;
    private MockUserProvider $users;
    private MockTrackingRepository $trackings;

    protected function setUp(): void
    {
        $this->scripts = new MockScriptLoader();
        $this->settings = new MockSettingsRepository();
        $this->context = new MockContext();
        $this->users = new MockUserProvider();
        $this->trackings = new MockTrackingRepository();
    }

    private function createPlugin(): Plugin
    {
        $hooks = new MockHooks();
        $posts = new MockPostQuery();
        $assets = new Assets(
            $this->scripts,
            dirname(__DIR__, 2) . '/',
            'http://example.com/',
            '1.0.0',
            $this->settings,
            $posts
        );
        $trackingService = new TrackingService($this->users, $this->trackings, $this->settings);
        $query = new SuggestionsQuery($this->settings, $posts);
        $suggestions = new SuggestionsEndpoint($query, new MockJsonResponse());
        $shortcode = new WidgetShortcode($assets, $trackingService, $query, $posts);
        $adminPage = new AdminPage($this->settings);
        $trackingEndpoint = new TrackingEndpoint($trackingService, new MockJsonResponse());

        return new Plugin(
            $assets,
            $this->settings,
            $this->context,
            $suggestions,
            $shortcode,
            $adminPage,
            $hooks,
            $trackingService,
            $trackingEndpoint
        );
    }

    public function testEnqueuesDetectorForConfiguredPostType(): void
    {
        $this->settings = new MockSettingsRepository(['post_types' => ['post', 'page']]);
        $this->context->singularPostType = 'post';
        $this->context->postId = 123;

        $plugin = $this->createPlugin();
        $plugin->enqueueDetector();

        $this->assertArrayHasKey(Assets::HANDLE_DETECTOR, $this->scripts->scripts);
    }

    public function testDoesNotEnqueueDetectorForNonConfiguredPostType(): void
    {
        $this->settings = new MockSettingsRepository(['post_types' => ['post']]);
        $this->context->singularPostType = 'page';
        $this->context->postId = 123;

        $plugin = $this->createPlugin();
        $plugin->enqueueDetector();

        $this->assertArrayNotHasKey(Assets::HANDLE_DETECTOR, $this->scripts->scripts);
    }

    public function testDoesNotEnqueueDetectorOnNonSingularPage(): void
    {
        $this->settings = new MockSettingsRepository(['post_types' => ['post']]);
        $this->context->singularPostType = null;
        $this->context->postId = 0;

        $plugin = $this->createPlugin();
        $plugin->enqueueDetector();

        $this->assertArrayNotHasKey(Assets::HANDLE_DETECTOR, $this->scripts->scripts);
    }

    public function testTracksViewedWhenServerSideEnabled(): void
    {
        $this->settings = new MockSettingsRepository([
            'post_types' => ['post'],
            'track_logged_in_users' => true,
        ]);
        $this->users->userId = 42;
        $this->context->singularPostType = 'post';
        $this->context->postId = 123;

        $plugin = $this->createPlugin();
        $plugin->enqueueDetector();

        $this->assertEquals('viewed', $this->trackings->history[42][123]);
    }

    public function testDoesNotTrackViewedWhenServerSideDisabled(): void
    {
        $this->settings = new MockSettingsRepository([
            'post_types' => ['post'],
            'track_logged_in_users' => false,
        ]);
        $this->users->userId = 42;
        $this->context->singularPostType = 'post';
        $this->context->postId = 123;

        $plugin = $this->createPlugin();
        $plugin->enqueueDetector();

        $this->assertEmpty($this->trackings->history);
    }

    public function testDoesNotTrackViewedForAnonymousUser(): void
    {
        $this->settings = new MockSettingsRepository([
            'post_types' => ['post'],
            'track_logged_in_users' => true,
        ]);
        $this->users->userId = 0;
        $this->context->singularPostType = 'post';
        $this->context->postId = 123;

        $plugin = $this->createPlugin();
        $plugin->enqueueDetector();

        $this->assertEmpty($this->trackings->history);
    }
}
