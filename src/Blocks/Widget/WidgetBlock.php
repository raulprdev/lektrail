<?php

namespace Completionist\Blocks\Widget;

use Completionist\Assets;
use Completionist\Contracts\Hooks;
use Completionist\Contracts\PluginConfigRepository;
use Completionist\InstanceSettings;

class WidgetBlock
{
    private WidgetRenderer $renderer;
    private PluginConfigRepository $pluginConfigs;
    private Assets $assets;
    private string $pluginPath;

    public function __construct(
        WidgetRenderer $renderer,
        PluginConfigRepository $pluginConfigs,
        Assets $assets,
        string $pluginPath
    ) {
        $this->renderer = $renderer;
        $this->pluginConfigs = $pluginConfigs;
        $this->assets = $assets;
        $this->pluginPath = $pluginPath;
    }

    public function register(Hooks $hooks): void
    {
        $hooks->addAction('init', [$this, 'registerBlock']);
        $hooks->addAction('enqueue_block_editor_assets', [$this, 'enqueueEditorAssets']);
    }

    public function registerBlock(): void
    {
        register_block_type(
            $this->pluginPath . 'build/blocks/Widget',
            ['render_callback' => [$this, 'renderBlock']]
        );
    }

    public function enqueueEditorAssets(): void
    {
        $this->assets->enqueueWidgetForEditor();

        $config = $this->pluginConfigs->load();
        wp_add_inline_script(
            'wp-blocks',
            'window.completionistDefaults = ' . json_encode($config->toJsConfig()) . ';',
            'before'
        );
    }

    public function renderBlock(array $attributes): string
    {
        $overrides = InstanceSettings::fromBlockAttributes($attributes);
        return $this->renderer->render($overrides);
    }
}
