<?php

namespace Completionist\Tests;

use Completionist\Assets;
use Completionist\Settings;
use Completionist\Tests\Mocks\MockScriptLoader;
use PHPUnit\Framework\TestCase;

class AssetsTest extends TestCase {

    private string $pluginPath;
    private string $pluginUrl;
    private MockScriptLoader $loader;
    private Assets $assets;

    protected function setUp(): void {
        $this->pluginPath = dirname(__DIR__, 2) . '/';
        $this->pluginUrl = 'http://example.com/wp-content/plugins/completionist/';
        $this->loader = new MockScriptLoader();
        $this->assets = new Assets($this->loader, $this->pluginPath, $this->pluginUrl, '1.0.0');
    }

    public function testFileVersionReturnsTimestampForExistingFile(): void {
        $version = $this->assets->fileVersion('assets/js/detector.js');

        $this->assertIsNumeric($version);
        $this->assertNotEquals('1.0.0', $version);
    }

    public function testFileVersionReturnsFallbackForMissingFile(): void {
        $version = $this->assets->fileVersion('nonexistent/file.js');

        $this->assertEquals('1.0.0', $version);
    }

    public function testEnqueueDetectorLoadsStorageFirst(): void {
        $this->assets->enqueueDetector(123);

        $this->assertArrayHasKey(Assets::HANDLE_STORAGE, $this->loader->scripts);
        $this->assertArrayHasKey(Assets::HANDLE_DETECTOR, $this->loader->scripts);
    }

    public function testEnqueueDetectorAddsPostIdInlineScript(): void {
        $this->assets->enqueueDetector(456);

        $inline = $this->loader->inlineScripts[Assets::HANDLE_DETECTOR];
        $this->assertStringContainsString('456', $inline['code']);
        $this->assertEquals('before', $inline['position']);
    }

    public function testEnqueueWidgetLoadsStyleAndScript(): void {
        $this->assets->enqueueWidget(new Settings());

        $this->assertArrayHasKey(Assets::HANDLE_WIDGET, $this->loader->scripts);
        $this->assertArrayHasKey(Assets::HANDLE_WIDGET, $this->loader->styles);
    }

    public function testEnqueueWidgetPassesSettingsToScript(): void {
        $settings = Settings::fromArray([
            'max_viewed' => 7,
            'max_read' => 12,
            'max_suggestions' => 8,
        ]);

        $this->assets->enqueueWidget($settings);

        $inline = $this->loader->inlineScripts[Assets::HANDLE_WIDGET];
        $this->assertStringContainsString('"maxViewed":7', $inline['code']);
        $this->assertStringContainsString('"maxRead":12', $inline['code']);
        $this->assertStringContainsString('"maxSuggestions":8', $inline['code']);
    }

    public function testEnqueueWidgetPassesLabelsToScript(): void {
        $settings = Settings::fromArray([
            'label_continue' => 'Keep reading',
            'label_completed' => 'Done',
            'label_suggestions' => 'Try these',
            'label_empty' => 'Nothing yet',
            'label_loading' => 'Wait...',
        ]);

        $this->assets->enqueueWidget($settings);

        $inline = $this->loader->inlineScripts[Assets::HANDLE_WIDGET];
        $this->assertStringContainsString('Keep reading', $inline['code']);
        $this->assertStringContainsString('Done', $inline['code']);
        $this->assertStringContainsString('Try these', $inline['code']);
        $this->assertStringContainsString('Nothing yet', $inline['code']);
        $this->assertStringContainsString('Wait...', $inline['code']);
    }
}