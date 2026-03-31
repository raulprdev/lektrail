<?php

namespace LekTrail;

use LekTrail\Contracts\Hooks;
use LekTrail\Contracts\PostQuery;

class PreviewEndpoint
{
    public const NAMESPACE = 'lektrail/v1';
    public const ROUTE = '/preview';

    private PostQuery $postQuery;

    public function __construct(PostQuery $postQuery)
    {
        $this->postQuery = $postQuery;
    }

    public function register(Hooks $hooks): void
    {
        $hooks->addAction('rest_api_init', [$this, 'registerRoute']);
    }

    public function registerRoute(): void
    {
        register_rest_route(self::NAMESPACE, self::ROUTE, [
            'methods' => 'GET',
            'callback' => [$this, 'handle'],
            'permission_callback' => [$this, 'canAccess'],
        ]);
    }

    public function canAccess(): bool
    {
        return current_user_can('edit_posts');
    }

    private const DEFAULT_MAX = 3;

    public function handle(): array
    {
        $maxViewed = $this->getIntParam('maxViewed', self::DEFAULT_MAX);
        $maxRead = $this->getIntParam('maxRead', self::DEFAULT_MAX);
        $maxSuggestions = $this->getIntParam('maxSuggestions', self::DEFAULT_MAX);

        $total = $maxViewed + $maxRead + $maxSuggestions;
        $posts = $this->postQuery->getRecent($total);

        return [
            'viewed' => array_slice($posts, 0, $maxViewed),
            'read' => array_slice($posts, $maxViewed, $maxRead),
            'suggestions' => array_slice($posts, $maxViewed + $maxRead, $maxSuggestions),
        ];
    }

    private function getIntParam(string $name, int $default): int
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- REST API with permission_callback
        $value = isset($_GET[$name]) ? absint(wp_unslash($_GET[$name])) : null;
        if ($value === null || $value <= 0) {
            return $default;
        }
        return $value;
    }
}
