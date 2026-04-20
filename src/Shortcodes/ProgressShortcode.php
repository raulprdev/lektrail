<?php

namespace LekTrail\Shortcodes;

use LekTrail\Contracts\Hooks;
use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Renderers\ProgressRenderer;

class ProgressShortcode
{
    public const TAG = 'lektrail-progress';

    private ProgressRenderer $renderer;

    public function __construct(ProgressRenderer $renderer)
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
