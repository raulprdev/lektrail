<?php

namespace LekTrail\Blocks\ReadingList;

use LekTrail\Contracts\Hooks;
use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Renderers\ReadingListRenderer;

class ReadingListBlock
{
    private ReadingListRenderer $renderer;
    private string $pluginPath;

    public function __construct(ReadingListRenderer $renderer, string $pluginPath)
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
            $this->pluginPath . 'build/blocks/ReadingList',
            ['render_callback' => [$this, 'renderBlock']]
        );
    }

    public function renderBlock(array $attributes): string
    {
        $filter = ReadingFilter::fromArray([
            'category' => $attributes['category'] ?? null,
            'year' => $attributes['year'] ?? null,
            'status' => $attributes['status'] ?? null,
        ]);
        $wrapperAttributes = get_block_wrapper_attributes();
        return $this->renderer->render($filter, $wrapperAttributes);
    }
}
