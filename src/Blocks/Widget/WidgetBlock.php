<?php

namespace Completionist\Blocks\Widget;

use Completionist\Assets;
use Completionist\Contracts\Hooks;
use Completionist\InstanceSettings;
use Completionist\WidgetRenderer;

class WidgetBlock
{
    private WidgetRenderer $renderer;
    private Assets $assets;
    private string $pluginPath;

    public function __construct(WidgetRenderer $renderer, Assets $assets, string $pluginPath)
    {
        $this->renderer = $renderer;
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
        $this->assets->registerWidgetStyle();

        register_block_type(
            $this->pluginPath . 'build/blocks/Widget',
            ['render_callback' => [$this, 'renderBlock']]
        );
    }

    public function enqueueEditorAssets(): void
    {
        $this->assets->enqueueWidgetForEditor();
    }

    public function renderBlock(array $attributes): string
    {
        $overrides = InstanceSettings::fromBlockAttributes($attributes);
        $wrapperAttributes = get_block_wrapper_attributes();
        return $this->renderer->render($overrides, $wrapperAttributes);
    }
}
