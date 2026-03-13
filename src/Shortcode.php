<?php

namespace Completionist;

use Completionist\Contracts\Hooks;

class Shortcode {

    public const TAG = 'completionist';
    public const WIDGET_ID = 'completionist-widget';

    private Assets $assets;
    private TrackingService $tracking;
    private SuggestionsQuery $suggestions;

    public function __construct(Assets $assets, TrackingService $tracking, SuggestionsQuery $suggestions) {
        $this->assets = $assets;
        $this->tracking = $tracking;
        $this->suggestions = $suggestions;
    }

    public function register(Hooks $hooks): void {
        $hooks->addShortcode(self::TAG, [$this, 'render']);
    }

    public function render(array $atts): string {
        $inlineData = null;

        if ($this->tracking->shouldTrackServerSide()) {
            $history = $this->tracking->getHistory();
            $excludeIds = array_merge(
                array_column($history['viewed'], 'id'),
                array_column($history['read'], 'id')
            );
            $inlineData = [
                'viewed' => $history['viewed'],
                'read' => $history['read'],
                'suggestions' => $this->suggestions->get($excludeIds),
            ];
        }

        $this->assets->enqueueWidget($inlineData);

        return sprintf(
            '<div id="%s" data-endpoint="%s" data-posts-endpoint="%s"></div>',
            self::WIDGET_ID,
            esc_url(SuggestionsEndpoint::url()),
            esc_url(rest_url('wp/v2/posts'))
        );
    }
}