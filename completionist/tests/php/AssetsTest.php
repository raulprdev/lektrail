<?php

namespace Completionist\Tests;

use Completionist\Assets;
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

        $this->assertArrayHasKey('completionist-storage', $this->loader->scripts);
        $this->assertArrayHasKey('completionist-detector', $this->loader->scripts);
    }

    public function testEnqueueDetectorAddsPostIdInlineScript(): void {
        $this->assets->enqueueDetector(456);

        $inline = $this->loader->inlineScripts['completionist-detector'];
        $this->assertStringContainsString('456', $inline['code']);
        $this->assertEquals('before', $inline['position']);
    }

    public function testEnqueueWidgetLoadsStyleAndScript(): void {
        $this->assets->enqueueWidget();

        $this->assertArrayHasKey('completionist-widget', $this->loader->scripts);
        $this->assertArrayHasKey('completionist-widget', $this->loader->styles);
    }
}