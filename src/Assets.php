<?php

namespace Completionist;

class Assets {

    public const HANDLE_STORAGE = 'completionist-storage';
    public const HANDLE_DETECTOR = 'completionist-detector';
    public const HANDLE_WIDGET = 'completionist-widget';

    private ScriptLoader $scripts;
    private string $pluginPath;
    private string $pluginUrl;
    private string $version;

    public function __construct(ScriptLoader $scripts, string $pluginPath, string $pluginUrl, string $version) {
        $this->scripts = $scripts;
        $this->pluginPath = $pluginPath;
        $this->pluginUrl = $pluginUrl;
        $this->version = $version;
    }

    public function enqueueDetector(int $postId): void {
        $this->enqueueStorage();

        $this->scripts->enqueueScript(
            self::HANDLE_DETECTOR,
            $this->pluginUrl . 'assets/js/detector.js',
            [self::HANDLE_STORAGE],
            $this->fileVersion('assets/js/detector.js'),
            true
        );

        $this->scripts->addInlineScript(
            self::HANDLE_DETECTOR,
            sprintf('document.body.dataset.completionistPost = %d;', $postId),
            'before'
        );
    }

    public function enqueueWidget(Settings $settings): void {
        $this->enqueueStorage();

        $this->scripts->enqueueScript(
            self::HANDLE_WIDGET,
            $this->pluginUrl . 'assets/js/widget.js',
            [self::HANDLE_STORAGE],
            $this->fileVersion('assets/js/widget.js'),
            true
        );

        $config = 'window.CompletionistConfig = ' . json_encode($settings->toJsConfig()) . ';';
        $this->scripts->addInlineScript(self::HANDLE_WIDGET, $config, 'before');

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

    public function fileVersion(string $relativePath): string {
        $file = $this->pluginPath . $relativePath;
        if (file_exists($file)) {
            return (string) filemtime($file);
        }
        return $this->version;
    }
}