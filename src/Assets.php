<?php

namespace Completionist;

use Completionist\Contracts\PluginConfigRepository;
use Completionist\Contracts\PostQuery;
use Completionist\Contracts\ScriptLoader;

class Assets
{
    public const HANDLE_STORAGE = 'completionist-storage';
    public const HANDLE_DETECTOR = 'completionist-detector';
    public const HANDLE_RENDER_ITEM = 'completionist-render-item';
    public const HANDLE_DATA_PROVIDER = 'completionist-data-provider';
    public const HANDLE_DATA_SOURCE = 'completionist-data-source';
    public const HANDLE_WIDGET = 'completionist-widget';
    public const HANDLE_CONSENT_BUILTIN = 'completionist-consent-builtin';
    public const HANDLE_CONSENT_MANAGER = 'completionist-consent-manager';
    public const HANDLE_NOTIFIER = 'completionist-notifier';

    private ScriptLoader $scripts;
    private string $pluginPath;
    private string $pluginUrl;
    private string $version;
    private PluginConfigRepository $pluginConfigs;
    private PostQuery $postQuery;

    public function __construct(ScriptLoader $scripts, string $pluginPath, string $pluginUrl, string $version, PluginConfigRepository $pluginConfigs, PostQuery $postQuery)
    {
        $this->scripts = $scripts;
        $this->pluginPath = $pluginPath;
        $this->pluginUrl = $pluginUrl;
        $this->version = $version;
        $this->pluginConfigs = $pluginConfigs;
        $this->postQuery = $postQuery;
    }

    public function enqueueDetector(int $postId, bool $serverSideTracking = false): void
    {
        $pluginConfig = $this->pluginConfigs->load();
        $this->enqueueStorage();
        $this->enqueueNotifier();

        $deps = [self::HANDLE_STORAGE, self::HANDLE_NOTIFIER];
        if ($pluginConfig->requireConsent() && !$serverSideTracking) {
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
            $postData['excerpt'] = $this->trimExcerpt($postData['excerpt'], $pluginConfig);
        }
        $this->scripts->addInlineScript(
            self::HANDLE_DETECTOR,
            sprintf('window.CompletionistPostData = %s;', json_encode($postData)),
            'before'
        );

        $config = $pluginConfig->toJsConfig();
        if ($serverSideTracking) {
            $config['trackingEndpoint'] = admin_url('admin-ajax.php?action=' . TrackingEndpoint::ACTION);
        }

        $this->scripts->addInlineScript(
            self::HANDLE_DETECTOR,
            sprintf('window.CompletionistConfig = %s;', json_encode($config)),
            'before'
        );
    }

    private function trimExcerpt(string $excerpt, PluginConfig $pluginConfig): string
    {
        $words = explode(' ', $excerpt);
        $length = $pluginConfig->excerptLength();
        if (count($words) <= $length) {
            return $excerpt;
        }
        return implode(' ', array_slice($words, 0, $length)) . '...';
    }

    public function enqueueWidget(?array $inlineData = null): void
    {
        $pluginConfig = $this->pluginConfigs->load();
        $this->enqueueStorage();
        $this->enqueueRenderItem();
        $this->enqueueDataProvider();
        $this->enqueueDataSource();

        $deps = [self::HANDLE_STORAGE, self::HANDLE_RENDER_ITEM, self::HANDLE_DATA_PROVIDER, self::HANDLE_DATA_SOURCE];
        if ($pluginConfig->requireConsent()) {
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

        $config = array_merge($pluginConfig->toJsConfig(), [
            'widgetId' => WidgetRenderer::WIDGET_ID,
        ]);

        if ($inlineData !== null) {
            $config['requireConsent'] = false;
            $config['serverSideTracking'] = true;
            $this->scripts->addInlineScript(
                self::HANDLE_WIDGET,
                'window.CompletionistInlineData = ' . json_encode($inlineData) . ';',
                'before'
            );
        }

        $this->scripts->addInlineScript(self::HANDLE_WIDGET, 'window.CompletionistConfig = ' . json_encode($config) . ';', 'before');

        $this->scripts->enqueueStyle(
            self::HANDLE_WIDGET,
            $this->pluginUrl . 'assets/css/widget.css',
            [],
            $this->fileVersion('assets/css/widget.css')
        );
    }

    public function enqueueWidgetForEditor(): void
    {
        $this->enqueueStorage();
        $this->enqueueRenderItem();
        $this->enqueueDataSource();

        $this->scripts->enqueueScript(
            self::HANDLE_WIDGET,
            $this->pluginUrl . 'assets/js/widget.js',
            [self::HANDLE_STORAGE, self::HANDLE_RENDER_ITEM, self::HANDLE_DATA_SOURCE],
            $this->fileVersion('assets/js/widget.js'),
            true
        );

        $this->scripts->enqueueStyle(
            self::HANDLE_WIDGET,
            $this->pluginUrl . 'assets/css/widget.css',
            [],
            $this->fileVersion('assets/css/widget.css')
        );

        $config = $this->pluginConfigs->load();
        $this->scripts->addInlineScript(
            'wp-blocks',
            'window.completionistDefaults = ' . json_encode($config->toJsConfig()) . ';',
            'before'
        );
    }

    private function enqueueStorage(): void
    {
        $this->scripts->enqueueScript(
            self::HANDLE_STORAGE,
            $this->pluginUrl . 'assets/js/storage.js',
            [],
            $this->fileVersion('assets/js/storage.js'),
            true
        );
    }

    private function enqueueNotifier(): void
    {
        $this->scripts->enqueueScript(
            self::HANDLE_NOTIFIER,
            $this->pluginUrl . 'assets/js/notifier.js',
            [],
            $this->fileVersion('assets/js/notifier.js'),
            true
        );
    }

    private function enqueueRenderItem(): void
    {
        $this->scripts->enqueueScript(
            self::HANDLE_RENDER_ITEM,
            $this->pluginUrl . 'assets/js/render-item.js',
            [],
            $this->fileVersion('assets/js/render-item.js'),
            true
        );
    }

    private function enqueueDataProvider(): void
    {
        $this->scripts->enqueueScript(
            self::HANDLE_DATA_PROVIDER,
            $this->pluginUrl . 'assets/js/data-provider.js',
            [self::HANDLE_STORAGE],
            $this->fileVersion('assets/js/data-provider.js'),
            true
        );
    }

    private function enqueueDataSource(): void
    {
        $this->scripts->enqueueScript(
            self::HANDLE_DATA_SOURCE,
            $this->pluginUrl . 'assets/js/data-source.js',
            [],
            $this->fileVersion('assets/js/data-source.js'),
            true
        );
    }

    public function fileVersion(string $relativePath): string
    {
        $file = $this->pluginPath . $relativePath;
        if (file_exists($file)) {
            return (string) filemtime($file);
        }
        return $this->version;
    }

    public function registerWidgetStyle(): void
    {
        $this->scripts->registerStyle(
            self::HANDLE_WIDGET,
            $this->pluginUrl . 'assets/css/widget.css',
            [],
            $this->fileVersion('assets/css/widget.css')
        );
    }

    private function enqueueConsent(): void
    {
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
