<?php

namespace LekTrail\Shortcodes;

use LekTrail\Contracts\Hooks;
use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Renderers\ReadingListRenderer;

class ReadingListShortcode
{
    public const TAG = 'lektrail-list';

    private ReadingListRenderer $renderer;

    public function __construct(ReadingListRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function register(Hooks $hooks): void
    {
        $hooks->addShortcode(self::TAG, [$this, 'render']);
    }

    public function render(array $atts): string
    {
        $filter = ReadingFilter::fromArray($atts);
        return $this->renderer->render($filter);
    }
}
