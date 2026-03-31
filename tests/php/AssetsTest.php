<?php

namespace LekTrail\Tests;

use LekTrail\Assets;
use LekTrail\Tests\Mocks\MockPluginConfigRepository;
use LekTrail\Tests\Mocks\MockPostQuery;
use LekTrail\Tests\Mocks\MockScriptLoader;
use PHPUnit\Framework\TestCase;

class AssetsTest extends TestCase
{
    private string $pluginPath;
    private string $pluginUrl;
    private MockScriptLoader $loader;
    private MockPostQuery $postQuery;

    protected function setUp(): void
    {
        $this->pluginPath = dirname(__DIR__, 2) . '/';
        $this->pluginUrl = 'http://example.com/wp-content/plugins/lektrail/';
        $this->loader = new MockScriptLoader();
        $this->postQuery = new MockPostQuery();
    }

    private function createAssets(array $configData = []): Assets
    {
        $pluginConfig = new MockPluginConfigRepository($configData);
        return new Assets($this->loader, $this->pluginPath, $this->pluginUrl, '1.0.0', $pluginConfig, $this->postQuery);
    }

    public function testFileVersionReturnsTimestampForExistingFile(): void
    {
        $assets = $this->createAssets();
        $version = $assets->fileVersion('assets/js/detector.js');

        $this->assertIsNumeric($version);
        $this->assertNotEquals('1.0.0', $version);
    }

    public function testFileVersionReturnsFallbackForMissingFile(): void
    {
        $assets = $this->createAssets();
        $version = $assets->fileVersion('nonexistent/file.js');

        $this->assertEquals('1.0.0', $version);
    }

    public function testEnqueueDetectorLoadsStorageFirst(): void
    {
        $assets = $this->createAssets();
        $assets->enqueueDetector(123);

        $this->assertArrayHasKey(Assets::HANDLE_STORAGE, $this->loader->scripts);
        $this->assertArrayHasKey(Assets::HANDLE_DETECTOR, $this->loader->scripts);
    }

    public function testEnqueueDetectorAddsPostIdInlineScript(): void
    {
        $assets = $this->createAssets();
        $assets->enqueueDetector(456);

        $inline = $this->loader->inlineScripts[Assets::HANDLE_DETECTOR];
        $this->assertStringContainsString('456', $inline['code']);
        $this->assertEquals('before', $inline['position']);
    }

    public function testEnqueueWidgetLoadsStyleAndScript(): void
    {
        $assets = $this->createAssets();
        $assets->enqueueWidget();

        $this->assertArrayHasKey(Assets::HANDLE_WIDGET, $this->loader->scripts);
        $this->assertArrayHasKey(Assets::HANDLE_WIDGET, $this->loader->styles);
    }

    public function testEnqueueWidgetPassesSettingsToScript(): void
    {
        $assets = $this->createAssets([
            'max_viewed' => 7,
            'max_read' => 12,
            'max_suggestions' => 8,
        ]);

        $assets->enqueueWidget();

        $inline = $this->loader->inlineScripts[Assets::HANDLE_WIDGET];
        $this->assertStringContainsString('"maxViewed":7', $inline['code']);
        $this->assertStringContainsString('"maxRead":12', $inline['code']);
        $this->assertStringContainsString('"maxSuggestions":8', $inline['code']);
    }

    public function testEnqueueWidgetPassesLabelsToScript(): void
    {
        $assets = $this->createAssets([
            'label_continue' => 'Keep reading',
            'label_completed' => 'Done',
            'label_suggestions' => 'Try these',
            'label_empty' => 'Nothing yet',
            'label_loading' => 'Wait...',
        ]);

        $assets->enqueueWidget();

        $inline = $this->loader->inlineScripts[Assets::HANDLE_WIDGET];
        $this->assertStringContainsString('Keep reading', $inline['code']);
        $this->assertStringContainsString('Done', $inline['code']);
        $this->assertStringContainsString('Try these', $inline['code']);
        $this->assertStringContainsString('Nothing yet', $inline['code']);
        $this->assertStringContainsString('Wait...', $inline['code']);
    }

    public function testEnqueueWidgetLoadsConsentScriptsWhenRequired(): void
    {
        $assets = $this->createAssets(['require_consent' => true]);

        $assets->enqueueWidget();

        $this->assertArrayHasKey(Assets::HANDLE_CONSENT_BUILTIN, $this->loader->scripts);
        $this->assertArrayHasKey(Assets::HANDLE_CONSENT_MANAGER, $this->loader->scripts);
    }

    public function testEnqueueWidgetSkipsConsentScriptsWhenNotRequired(): void
    {
        $assets = $this->createAssets(['require_consent' => false]);

        $assets->enqueueWidget();

        $this->assertArrayNotHasKey(Assets::HANDLE_CONSENT_BUILTIN, $this->loader->scripts);
        $this->assertArrayNotHasKey(Assets::HANDLE_CONSENT_MANAGER, $this->loader->scripts);
    }

    public function testEnqueueDetectorLoadsConsentScriptsWhenRequired(): void
    {
        $assets = $this->createAssets(['require_consent' => true]);

        $assets->enqueueDetector(123);

        $this->assertArrayHasKey(Assets::HANDLE_CONSENT_BUILTIN, $this->loader->scripts);
        $this->assertArrayHasKey(Assets::HANDLE_CONSENT_MANAGER, $this->loader->scripts);
    }

    public function testEnqueueDetectorSkipsConsentScriptsWhenNotRequired(): void
    {
        $assets = $this->createAssets(['require_consent' => false]);

        $assets->enqueueDetector(123);

        $this->assertArrayNotHasKey(Assets::HANDLE_CONSENT_BUILTIN, $this->loader->scripts);
    }

    public function testEnqueueDetectorSkipsConsentScriptsWhenServerSideTracking(): void
    {
        $assets = $this->createAssets(['require_consent' => true]);

        $assets->enqueueDetector(123, true);

        $this->assertArrayNotHasKey(Assets::HANDLE_CONSENT_BUILTIN, $this->loader->scripts);
        $this->assertArrayNotHasKey(Assets::HANDLE_CONSENT_MANAGER, $this->loader->scripts);
    }

    public function testEnqueueDetectorOutputsPostData(): void
    {
        $this->postQuery->postData[123] = [
            'id' => 123,
            'title' => 'Test Post',
            'url' => '/test-post',
            'excerpt' => 'This is the excerpt.',
            'thumbnail' => 'http://example.com/image.jpg',
        ];
        $assets = $this->createAssets();

        $assets->enqueueDetector(123);

        $inlineScripts = $this->loader->inlineScripts[Assets::HANDLE_DETECTOR];
        $this->assertStringContainsString('LekTrailPostData', $inlineScripts['code']);
        $this->assertStringContainsString('"title":"Test Post"', $inlineScripts['code']);
        $this->assertStringContainsString('"excerpt":"This is the excerpt."', $inlineScripts['code']);
        $this->assertStringContainsString('image.jpg', $inlineScripts['code']);
    }

    public function testEnqueueDetectorTrimsExcerptToConfiguredLength(): void
    {
        $longExcerpt = 'one two three four five six seven eight nine ten eleven twelve thirteen fourteen fifteen sixteen seventeen eighteen nineteen twenty twentyone twentytwo';
        $this->postQuery->postData[123] = [
            'id' => 123,
            'title' => 'Test Post',
            'url' => '/test-post',
            'excerpt' => $longExcerpt,
        ];
        $assets = $this->createAssets(['excerpt_length' => 10]);

        $assets->enqueueDetector(123);

        $inlineScripts = $this->loader->inlineScripts[Assets::HANDLE_DETECTOR];
        $this->assertStringContainsString('one two three four five six seven eight nine ten...', $inlineScripts['code']);
        $this->assertStringNotContainsString('eleven', $inlineScripts['code']);
    }

    public function testEnqueueWidgetDisablesConsentWhenInlineDataProvided(): void
    {
        $assets = $this->createAssets(['require_consent' => true]);
        $inlineData = ['viewed' => [], 'read' => [], 'suggestions' => []];

        $assets->enqueueWidget($inlineData);

        $inline = $this->loader->inlineScripts[Assets::HANDLE_WIDGET];
        $this->assertStringContainsString('"requireConsent":false', $inline['code']);
        $this->assertStringContainsString('"serverSideTracking":true', $inline['code']);
    }

    public function testEnqueueWidgetForEditorInjectsLekTrailConfig(): void
    {
        $assets = $this->createAssets();

        $assets->enqueueWidgetForEditor();

        $inline = $this->loader->inlineScripts[Assets::HANDLE_WIDGET];
        $this->assertStringContainsString('LekTrailConfig', $inline['code']);
    }
}
