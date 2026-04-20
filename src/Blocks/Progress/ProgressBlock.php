<?php

namespace LekTrail\Blocks\Progress;

use LekTrail\Contracts\Hooks;
use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Renderers\ProgressRenderer;

class ProgressBlock
{
    private ProgressRenderer $renderer;
    private string $pluginPath;

    public function __construct(ProgressRenderer $renderer, string $pluginPath)
    {
        $this->renderer = $renderer;
        $this->pluginPath = $pluginPath;
    }

    public function register(Hooks $hooks): void
    {
        $hooks->addAction('init', [$this, 'registerBlock']);
    }

    public function registerBlock(): void
    {
        register_block_type(
            $this->pluginPath . 'build/blocks/Progress',
            ['render_callback' => [$this, 'renderBlock']]
        );
    }

    public function renderBlock(array $attributes): string
    {
        $filter = ReadingFilter::fromArray([
            'category' => $attributes['category'] ?? null,
            'year' => $attributes['year'] ?? null,
        ]);
        $wrapperAttributes = get_block_wrapper_attributes();
        return $this->renderer->render($filter, $wrapperAttributes);
    }
}
