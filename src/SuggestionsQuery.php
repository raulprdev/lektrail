<?php

namespace LekTrail;

use LekTrail\Contracts\PluginConfigRepository;
use LekTrail\Contracts\PostQuery;

class SuggestionsQuery
{
    public const ORDER_RANDOM = 'random';
    public const ORDER_RECENT = 'recent';
    public const ORDER_RELATED = 'related';

    public static function validOrders(): array
    {
        return [self::ORDER_RANDOM, self::ORDER_RECENT, self::ORDER_RELATED];
    }

    private PluginConfigRepository $pluginConfigs;
    private PostQuery $postQuery;

    public function __construct(PluginConfigRepository $pluginConfigs, PostQuery $postQuery)
    {
        $this->pluginConfigs = $pluginConfigs;
        $this->postQuery     = $postQuery;
    }

    private const SHUFFLE_MULTIPLIER = 3;

    public function get(array $excludeIds, array $relatedCategories = []): array
    {
        $pluginConfig = $this->pluginConfigs->load();
        $limit = $pluginConfig->maxSuggestions();
        $order = $pluginConfig->suggestionOrder();
        $args = $this->buildQueryArgs($excludeIds, $relatedCategories, $limit);
        $posts = $this->postQuery->query($args);

        if ($order === self::ORDER_RANDOM || $order === self::ORDER_RELATED) {
            shuffle($posts);
        }

        return array_slice($posts, 0, $limit);
    }

    private function buildQueryArgs(array $excludeIds, array $relatedCategories, int $limit): array
    {
        $pluginConfig = $this->pluginConfigs->load();
        $order = $pluginConfig->suggestionOrder();
        $fetchLimit = $order === self::ORDER_RANDOM || $order === self::ORDER_RELATED
            ? $limit * self::SHUFFLE_MULTIPLIER
            : $limit;

        $args = [
            'post_type' => $pluginConfig->postTypes(),
            'post_status' => 'publish',
            'posts_per_page' => $fetchLimit,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if (!empty($excludeIds)) {
            $args['post__not_in'] = $excludeIds;
        }

        $this->applyCategoryFilters($args, $relatedCategories, $pluginConfig);

        return $args;
    }

    private function applyCategoryFilters(array &$args, array $relatedCategories, PluginConfig $pluginConfig): void
    {
        $order = $pluginConfig->suggestionOrder();

        if ($order === self::ORDER_RELATED) {
            if (!empty($relatedCategories)) {
                $args['category__in'] = $relatedCategories;
            }
            return;
        }

        $include = $pluginConfig->includeCategories();
        $exclude = $pluginConfig->excludeCategories();

        if (!empty($include)) {
            $args['category__in'] = $include;
        }
        if (!empty($exclude)) {
            $args['category__not_in'] = $exclude;
        }
    }
}
