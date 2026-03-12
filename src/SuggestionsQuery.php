<?php

namespace Completionist;

use Completionist\Contracts\PostQuery;

class SuggestionsQuery {

    public const ORDER_RANDOM = 'random';
    public const ORDER_RECENT = 'recent';
    public const ORDER_RELATED = 'related';

    private Settings $settings;
    private PostQuery $posts;

    public function __construct(Settings $settings, PostQuery $posts) {
        $this->settings = $settings;
        $this->posts = $posts;
    }

    public function get(array $excludeIds, array $relatedCategories = []): array {
        $args = $this->buildQueryArgs($excludeIds, $relatedCategories);
        return $this->posts->query($args);
    }

    private function buildQueryArgs(array $excludeIds, array $relatedCategories): array {
        $args = [
            'post_type' => $this->settings->postTypes(),
            'post_status' => 'publish',
            'posts_per_page' => $this->settings->maxSuggestions(),
        ];

        if (!empty($excludeIds)) {
            $args['post__not_in'] = $excludeIds;
        }

        $this->applyOrderStrategy($args, $relatedCategories);
        $this->applyCategoryFilters($args);

        return $args;
    }

    private function applyOrderStrategy(array &$args, array $relatedCategories): void {
        $order = $this->settings->suggestionOrder();

        if ($order === self::ORDER_RECENT) {
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            return;
        }

        if ($order === self::ORDER_RELATED && !empty($relatedCategories)) {
            $args['category__in'] = $relatedCategories;
        }

        $args['orderby'] = 'rand';
    }

    private function applyCategoryFilters(array &$args): void {
        if ($this->settings->suggestionOrder() === self::ORDER_RELATED) {
            return;
        }

        $include = $this->settings->includeCategories();
        $exclude = $this->settings->excludeCategories();

        if (!empty($include)) {
            $args['category__in'] = $include;
        }
        if (!empty($exclude)) {
            $args['category__not_in'] = $exclude;
        }
    }
}