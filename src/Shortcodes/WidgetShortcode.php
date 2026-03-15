<?php

namespace Completionist\Shortcodes;

use Completionist\Blocks\Widget\WidgetRenderer;
use Completionist\Contracts\Hooks;
use Completionist\InstanceSettings;

class WidgetShortcode
{
    public const TAG = 'completionist';

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