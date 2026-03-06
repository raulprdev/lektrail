<?php

namespace Completionist;

class Assets {

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
            'completionist-detector',
            $this->pluginUrl . 'assets/js/detector.js',
            ['completionist-storage'],
            $this->fileVersion('assets/js/detector.js'),
            true
        );

        $this->scripts->addInlineScript(
            'completionist-detector',
            sprintf('document.body.dataset.completionistPost = %d;', $postId),
            'before'
        );
    }

    public function enqueueWidget(): void {
        $this->enqueueStorage();

        $this->scripts->enqueueScript(
            'completionist-widget',
            $this->pluginUrl . 'assets/js/widget.js',
            ['completionist-storage'],
            $this->fileVersion('assets/js/widget.js'),
            true
        );

        $this->scripts->enqueueStyle(
            'completionist-widget',
            $this->pluginUrl . 'assets/css/widget.css',
            [],
            $this->fileVersion('assets/css/widget.css')
        );
    }

    private function enqueueStorage(): void {
        $this->scripts->enqueueScript(
            'completionist-storage',
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