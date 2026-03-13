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

    private const SHUFFLE_MULTIPLIER = 3;

    public function get(array $excludeIds, array $relatedCategories = []): array {
        $settings = $this->settings->load();
        $limit = $settings->maxSuggestions();
        $order = $settings->suggestionOrder();
        $args = $this->buildQueryArgs($excludeIds, $relatedCategories, $limit);
        $posts = $this->posts->query($args);

        if ($order === self::ORDER_RANDOM || $order === self::ORDER_RELATED) {
            shuffle($posts);
        }

        return array_slice($posts, 0, $limit);
    }

    private function buildQueryArgs(array $excludeIds, array $relatedCategories, int $limit): array {
        $settings = $this->settings->load();
        $order = $settings->suggestionOrder();
        $fetchLimit = $order === self::ORDER_RANDOM || $order === self::ORDER_RELATED
            ? $limit * self::SHUFFLE_MULTIPLIER
            : $limit;

        $args = [
            'post_type' => $settings->postTypes(),
            'post_status' => 'publish',
            'posts_per_page' => $fetchLimit,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if (!empty($excludeIds)) {
            $args['post__not_in'] = $excludeIds;
        }

        $this->applyCategoryFilters($args, $relatedCategories, $settings);

        return $args;
    }

    private function applyCategoryFilters(array &$args, array $relatedCategories, Settings $settings): void {
        $order = $settings->suggestionOrder();

        if ($order === self::ORDER_RELATED) {
            if (!empty($relatedCategories)) {
                $args['category__in'] = $relatedCategories;
            }
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