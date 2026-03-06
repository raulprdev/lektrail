<?php

namespace Completionist;

class Shortcode {

    public const TAG = 'completionist';

    private Assets $assets;
    private SettingsRepository $settings;

    public function __construct(Assets $assets, SettingsRepository $settings) {
        $this->assets = $assets;
        $this->settings = $settings;
    }

    public function register(Hooks $hooks): void {
        $hooks->addShortcode(self::TAG, [$this, 'render']);
    }

    public function render(array $atts): string {
        $settings = $this->settings->load();
        $this->assets->enqueueWidget($settings);

        return sprintf(
            '<div id="completionist-widget" data-endpoint="%s" data-posts-endpoint="%s"></div>',
            esc_url(SuggestionsEndpoint::url()),
            esc_url(rest_url('wp/v2/posts'))
        );
    }
}