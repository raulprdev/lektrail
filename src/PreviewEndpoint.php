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

    public function handle(): array
    {
        $posts = $this->postQuery->getRecent(9);

        return [
            'viewed' => array_slice($posts, 0, 3),
            'read' => array_slice($posts, 3, 3),
            'suggestions' => array_slice($posts, 6, 3),
        ];
    }
}