<?php

namespace Completionist;

use Completionist\Contracts\PostQuery;
use Completionist\Contracts\ScriptLoader;

class Assets {

    public const HANDLE_STORAGE = 'completionist-storage';
    public const HANDLE_DETECTOR = 'completionist-detector';
    public const HANDLE_RENDER_ITEM = 'completionist-render-item';
    public const HANDLE_DATA_PROVIDER = 'completionist-data-provider';
    public const HANDLE_WIDGET = 'completionist-widget';
    public const HANDLE_CONSENT_BUILTIN = 'completionist-consent-builtin';
    public const HANDLE_CONSENT_MANAGER = 'completionist-consent-manager';

    private ScriptLoader $scripts;
    private string $pluginPath;
    private string $pluginUrl;
    private string $version;
    private Settings $settings;
    private PostQuery $postQuery;

    public function __construct(ScriptLoader $scripts, string $pluginPath, string $pluginUrl, string $version, Settings $settings, PostQuery $postQuery) {
        $this->scripts = $scripts;
        $this->pluginPath = $pluginPath;
        $this->pluginUrl = $pluginUrl;
        $this->version = $version;
        $this->settings = $settings;
        $this->postQuery = $postQuery;
    }

    public function enqueueDetector(int $postId): void {
        $this->enqueueStorage();

        $deps = [self::HANDLE_STORAGE];
        if ($this->settings->requireConsent()) {
            $this->enqueueConsent();
            $deps[] = self::HANDLE_CONSENT_MANAGER;
        }

        $this->scripts->enqueueScript(
            self::HANDLE_DETECTOR,
            $this->pluginUrl . 'assets/js/detector.js',
            $deps,
            $this->fileVersion('assets/js/detector.js'),
            true
        );

        $this->scripts->addInlineScript(
            self::HANDLE_DETECTOR,
            sprintf('document.body.dataset.completionistPost = %d;', $postId),
            'before'
        );

        $postData = $this->postQuery->getPostData($postId);
        if (isset($postData['excerpt'])) {
            $postData['excerpt'] = $this->trimExcerpt($postData['excerpt']);
        }
        $this->scripts->addInlineScript(
            self::HANDLE_DETECTOR,
            sprintf('window.CompletionistPostData = %s;', json_encode($postData)),
            'before'
        );

        $this->scripts->addInlineScript(
            self::HANDLE_DETECTOR,
            sprintf('window.CompletionistConfig = %s;', json_encode($this->settings->toJsConfig())),
            'before'
        );
    }

    private function trimExcerpt(string $excerpt): string {
        $words = explode(' ', $excerpt);
        $length = $this->settings->excerptLength();
        if (count($words) <= $length) {
            return $excerpt;
        }
        return implode(' ', array_slice($words, 0, $length)) . '...';
    }

    public function enqueueWidget(): void {
        $this->enqueueStorage();
        $this->enqueueRenderItem();
        $this->enqueueDataProvider();

        $deps = [self::HANDLE_STORAGE, self::HANDLE_RENDER_ITEM, self::HANDLE_DATA_PROVIDER];
        if ($this->settings->requireConsent()) {
            $this->enqueueConsent();
            $deps[] = self::HANDLE_CONSENT_MANAGER;
        }

        $this->scripts->enqueueScript(
            self::HANDLE_WIDGET,
            $this->pluginUrl . 'assets/js/widget.js',
            $deps,
            $this->fileVersion('assets/js/widget.js'),
            true
        );

        $config = array_merge($this->settings->toJsConfig(), [
            'widgetId' => Shortcode::WIDGET_ID,
        ]);
        $this->scripts->addInlineScript(self::HANDLE_WIDGET, 'window.CompletionistConfig = ' . json_encode($config) . ';', 'before');

        $this->scripts->enqueueStyle(
            self::HANDLE_WIDGET,
            $this->pluginUrl . 'assets/css/widget.css',
            [],
            $this->fileVersion('assets/css/widget.css')
        );
    }

    private function enqueueStorage(): void {
        $this->scripts->enqueueScript(
            self::HANDLE_STORAGE,
            $this->pluginUrl . 'assets/js/storage.js',
            [],
            $this->fileVersion('assets/js/storage.js'),
            true
        );
    }

    private function enqueueRenderItem(): void {
        $this->scripts->enqueueScript(
            self::HANDLE_RENDER_ITEM,
            $this->pluginUrl . 'assets/js/render-item.js',
            [],
            $this->fileVersion('assets/js/render-item.js'),
            true
        );
    }

    private function enqueueDataProvider(): void {
        $this->scripts->enqueueScript(
            self::HANDLE_DATA_PROVIDER,
            $this->pluginUrl . 'assets/js/data-provider.js',
            [self::HANDLE_STORAGE],
            $this->fileVersion('assets/js/data-provider.js'),
            true
        );
    }

    public function fileVersion(string $relativePath): string {
        $file = $this->pluginPath . $relativePath;
        if (file_exists($file)) {
            return (string) filemtime($file);
        }
        return $this->version;
    }

    private function enqueueConsent(): void {
        $this->scripts->enqueueScript(
            self::HANDLE_CONSENT_BUILTIN,
            $this->pluginUrl . 'assets/js/consent/builtin-provider.js',
            [],
            $this->fileVersion('assets/js/consent/builtin-provider.js'),
            true
        );

        $this->scripts->enqueueScript(
            self::HANDLE_CONSENT_MANAGER,
            $this->pluginUrl . 'assets/js/consent/consent-manager.js',
            [self::HANDLE_CONSENT_BUILTIN],
            $this->fileVersion('assets/js/consent/consent-manager.js'),
            true
        );
    }
}