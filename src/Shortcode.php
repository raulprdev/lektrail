<?php

namespace Completionist;

class Shortcode {

    public const TAG = 'completionist';
    public const WIDGET_ID = 'completionist-widget';

    private Assets $assets;

    public function __construct(Assets $assets) {
        $this->assets = $assets;
    }

    public function register(Hooks $hooks): void {
        $hooks->addShortcode(self::TAG, [$this, 'render']);
    }

    public function render(array $atts): string {
        $this->assets->enqueueWidget();

        return sprintf(
            '<div id="%s" data-endpoint="%s" data-posts-endpoint="%s"></div>',
            self::WIDGET_ID,
            esc_url(SuggestionsEndpoint::url()),
            esc_url(rest_url('wp/v2/posts'))
        );
    }
}