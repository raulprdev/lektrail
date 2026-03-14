<?php

namespace Completionist\Blocks\Widget;

use Completionist\Contracts\Hooks;

class WidgetBlock
{
    private WidgetRenderer $renderer;

    public function __construct(WidgetRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function register(Hooks $hooks): void
    {
        $hooks->addAction('init', [$this, 'registerBlock']);
    }

    public function registerBlock(): void
    {
        register_block_type(
            COMPLETIONIST_PLUGIN_PATH . 'build/blocks/Widget',
            ['render_callback' => [$this->renderer, 'render']]
        );
    }
}