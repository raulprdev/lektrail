<?php

namespace Completionist;

use Completionist\Contracts\Hooks;
use Completionist\Contracts\PostQuery;

class PreviewEndpoint
{
    public const NAMESPACE = 'completionist/v1';
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
        $value = $_GET[$name] ?? null;
        if ($value === null) {
            return $default;
        }
        $int = (int) $value;
        return $int > 0 ? $int : $default;
    }
}
