<?php

namespace Completionist\Blocks\Widget;

use Completionist\Assets;
use Completionist\Contracts\PostQuery;
use Completionist\SuggestionsEndpoint;
use Completionist\SuggestionsQuery;
use Completionist\TrackingService;

class WidgetRenderer
{
    public const WIDGET_ID = 'completionist-widget';

    private Assets $assets;
    private TrackingService $trackingService;
    private SuggestionsQuery $suggestionsQuery;
    private PostQuery $postQuery;

    public function __construct(
        Assets $assets,
        TrackingService $trackingService,
        SuggestionsQuery $suggestionsQuery,
        PostQuery $postQuery
    ) {
        $this->assets = $assets;
        $this->trackingService = $trackingService;
        $this->suggestionsQuery = $suggestionsQuery;
        $this->postQuery = $postQuery;
    }

    public function render(): string
    {
        $inlineData = null;

        if ($this->trackingService->shouldTrackServerSide()) {
            $history = $this->trackingService->getHistory();
            $excludeIds = array_merge(
                array_column($history['viewed'], 'id'),
                array_column($history['read'], 'id')
            );
            $inlineData = [
                'viewed' => $this->enrichPosts($history['viewed']),
                'read' => $this->enrichPosts($history['read']),
                'suggestions' => $this->suggestionsQuery->get($excludeIds),
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

    private function enrichPosts(array $posts): array
    {
        $ids = array_column($posts, 'id');
        return $this->postQuery->getPostsDataByIds($ids);
    }
}