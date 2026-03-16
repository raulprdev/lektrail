<?php

namespace Completionist;

use Completionist\Contracts\PluginConfigRepository;
use Completionist\Contracts\PostQuery;

class WidgetRenderer
{
    public const WIDGET_ID = 'completionist-widget';

    private Assets $assets;
    private TrackingService $trackingService;
    private SuggestionsQuery $suggestionsQuery;
    private PostQuery $postQuery;
    private PluginConfigRepository $pluginConfigs;

    public function __construct(
        Assets $assets,
        TrackingService $trackingService,
        SuggestionsQuery $suggestionsQuery,
        PostQuery $postQuery,
        PluginConfigRepository $pluginConfigs
    ) {
        $this->assets = $assets;
        $this->trackingService = $trackingService;
        $this->suggestionsQuery = $suggestionsQuery;
        $this->postQuery = $postQuery;
        $this->pluginConfigs = $pluginConfigs;
    }

    public function render(array $overrides = [], string $wrapperAttributes = ''): string
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

        $configAttr = '';
        if (!empty($overrides)) {
            $globalConfig = $this->pluginConfigs->load();
            $mergedConfig = InstanceSettings::merge($globalConfig, $overrides);
            $configAttr = sprintf(' data-config="%s"', esc_attr(json_encode($mergedConfig->toJsConfig())));
        }

        if ($wrapperAttributes) {
            return sprintf(
                '<div %s id="%s" data-endpoint="%s" data-posts-endpoint="%s"%s></div>',
                $wrapperAttributes,
                self::WIDGET_ID,
                esc_url(SuggestionsEndpoint::url()),
                esc_url(rest_url('wp/v2/posts')),
                $configAttr
            );
        }

        return sprintf(
            '<div id="%s" class="%s" data-endpoint="%s" data-posts-endpoint="%s"%s></div>',
            self::WIDGET_ID,
            self::WIDGET_ID,
            esc_url(SuggestionsEndpoint::url()),
            esc_url(rest_url('wp/v2/posts')),
            $configAttr
        );
    }

    private function enrichPosts(array $posts): array
    {
        $ids = array_column($posts, 'id');
        return $this->postQuery->getPostsDataByIds($ids);
    }
}
