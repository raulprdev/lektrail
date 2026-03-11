<?php

namespace Completionist\Tests;

use Completionist\AdminPage;
use Completionist\Assets;
use Completionist\Plugin;
use Completionist\Settings;
use Completionist\Shortcode;
use Completionist\SuggestionsEndpoint;
use Completionist\Tests\Mocks\MockContext;
use Completionist\Tests\Mocks\MockHooks;
use Completionist\Tests\Mocks\MockJsonResponse;
use Completionist\Tests\Mocks\MockPostQuery;
use Completionist\Tests\Mocks\MockScriptLoader;
use Completionist\Tests\Mocks\MockSettingsRepository;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase {

    private MockScriptLoader $scripts;
    private MockSettingsRepository $settings;
    private MockContext $context;
    private Assets $assets;

    protected function setUp(): void {
        $this->scripts = new MockScriptLoader();
        $this->settings = new MockSettingsRepository();
        $this->context = new MockContext();
        $this->assets = new Assets(
            $this->scripts,
            dirname(__DIR__, 2) . '/',
            'http://example.com/',
            '1.0.0',
            new Settings(),
            new MockPostQuery()
        );
    }

    private function createPlugin(): Plugin {
        $hooks = new MockHooks();
        $suggestions = new SuggestionsEndpoint(new MockPostQuery(), new MockJsonResponse());
        $shortcode = new Shortcode($this->assets);
        $adminPage = new AdminPage($this->settings);

        return new Plugin(
            $this->assets,
            $this->settings,
            $this->context,
            $suggestions,
            $shortcode,
            $adminPage,
            $hooks
        );
    }

    public function testEnqueuesDetectorForConfiguredPostType(): void {
        $this->settings->settings = Settings::fromArray(['post_types' => ['post', 'page']]);
        $this->context->singularPostType = 'post';
        $this->context->postId = 123;

        $plugin = $this->createPlugin();
        $plugin->enqueueDetector();

        $this->assertArrayHasKey(Assets::HANDLE_DETECTOR, $this->scripts->scripts);
    }

    public function testDoesNotEnqueueDetectorForNonConfiguredPostType(): void {
        $this->settings->settings = Settings::fromArray(['post_types' => ['post']]);
        $this->context->singularPostType = 'page';
        $this->context->postId = 123;

        $plugin = $this->createPlugin();
        $plugin->enqueueDetector();

        $this->assertArrayNotHasKey(Assets::HANDLE_DETECTOR, $this->scripts->scripts);
    }

    public function testDoesNotEnqueueDetectorOnNonSingularPage(): void {
        $this->settings->settings = Settings::fromArray(['post_types' => ['post']]);
        $this->context->singularPostType = null;
        $this->context->postId = 0;

        $plugin = $this->createPlugin();
        $plugin->enqueueDetector();

        $this->assertArrayNotHasKey(Assets::HANDLE_DETECTOR, $this->scripts->scripts);
    }
}