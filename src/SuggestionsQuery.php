<?php

namespace Completionist;

use Completionist\Contracts\PostQuery;
use Completionist\Contracts\SettingsRepository;

class SuggestionsQuery {

    public const ORDER_RANDOM = 'random';
    public const ORDER_RECENT = 'recent';
    public const ORDER_RELATED = 'related';

    private SettingsRepository $settings;
    private PostQuery $posts;

    public function __construct(SettingsRepository $settings, PostQuery $posts) {
        $this->settings = $settings;
        $this->posts = $posts;
    }

    public function get(array $excludeIds, array $relatedCategories = []): array {
        $args = $this->buildQueryArgs($excludeIds, $relatedCategories);
        return $this->posts->query($args);
    }

    private function buildQueryArgs(array $excludeIds, array $relatedCategories): array {
        $settings = $this->settings->load();
        $args = [
            'post_type' => $settings->postTypes(),
            'post_status' => 'publish',
            'posts_per_page' => $settings->maxSuggestions(),
        ];

        if (!empty($excludeIds)) {
            $args['post__not_in'] = $excludeIds;
        }

        $this->applyOrderStrategy($args, $relatedCategories, $settings);
        $this->applyCategoryFilters($args, $settings);

        return $args;
    }

    private function applyOrderStrategy(array &$args, array $relatedCategories, Settings $settings): void {
        $order = $settings->suggestionOrder();

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

    private function applyCategoryFilters(array &$args, Settings $settings): void {
        if ($settings->suggestionOrder() === self::ORDER_RELATED) {
            return;
        }

        $include = $settings->includeCategories();
        $exclude = $settings->excludeCategories();

        if (!empty($include)) {
            $args['category__in'] = $include;
        }
        if (!empty($exclude)) {
            $args['category__not_in'] = $exclude;
        }
    }
}