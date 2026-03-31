<?php

namespace LekTrail\Shortcodes;

use LekTrail\Contracts\Hooks;
use LekTrail\InstanceSettings;
use LekTrail\WidgetRenderer;

class WidgetShortcode
{
    public const TAG = 'lektrail';

    private WidgetRenderer $renderer;

    public function __construct(WidgetRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function register(Hooks $hooks): void
    {
        $hooks->addShortcode(self::TAG, [$this, 'render']);
    }

    public function render(array $atts): string
    {
        $overrides = InstanceSettings::fromShortcodeAttributes($atts);
        return $this->renderer->render($overrides);
    }
}
