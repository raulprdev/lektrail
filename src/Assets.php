<?php

namespace Completionist;

class Assets {

    public const HANDLE_STORAGE = 'completionist-storage';
    public const HANDLE_DETECTOR = 'completionist-detector';
    public const HANDLE_RENDER_ITEM = 'completionist-render-item';
    public const HANDLE_WIDGET = 'completionist-widget';
    public const HANDLE_CONSENT_BUILTIN = 'completionist-consent-builtin';
    public const HANDLE_CONSENT_MANAGER = 'completionist-consent-manager';

    private ScriptLoader $scripts;
    private string $pluginPath;
    private string $pluginUrl;
    private string $version;
    private Settings $settings;

    public function __construct(ScriptLoader $scripts, string $pluginPath, string $pluginUrl, string $version, Settings $settings) {
        $this->scripts = $scripts;
        $this->pluginPath = $pluginPath;
        $this->pluginUrl = $pluginUrl;
        $this->version = $version;
        $this->settings = $settings;
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
    }

    public function enqueueWidget(): void {
        $this->enqueueStorage();
        $this->enqueueRenderItem();

        $deps = [self::HANDLE_STORAGE, self::HANDLE_RENDER_ITEM];
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