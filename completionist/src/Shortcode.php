<?php

namespace Completionist;

class Shortcode {

    public const TAG = 'completionist';
    public const DEFAULT_COUNT = 5;

    private Assets $assets;

    public function __construct(Assets $assets) {
        $this->assets = $assets;
    }

    public function register(Hooks $hooks): void {
        $hooks->addShortcode(self::TAG, [$this, 'render']);
    }

    public function render(array $atts): string {
        $count = isset($atts['count']) ? (int) $atts['count'] : self::DEFAULT_COUNT;

        $this->assets->enqueueWidget();

        return sprintf(
            '<div id="completionist-widget" data-count="%d" data-endpoint="%s"></div>',
            $count,
            esc_url(SuggestionsEndpoint::url())
        );
    }
}