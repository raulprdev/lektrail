<?php

namespace Completionist;

class SuggestionsEndpoint {

    public const ACTION = 'completionist_suggestions';
    public const MAX_COUNT = 20;
    public const DEFAULT_COUNT = 5;

    private PostQuery $posts;
    private JsonResponse $response;

    public function __construct(PostQuery $posts, JsonResponse $response) {
        $this->posts = $posts;
        $this->response = $response;
    }

    public function register(Hooks $hooks): void {
        $hooks->addAction('wp_ajax_' . self::ACTION, [$this, 'handle']);
        $hooks->addAction('wp_ajax_nopriv_' . self::ACTION, [$this, 'handle']);
    }

    public function handle(): void {
        $count = $this->parseCount($_GET['count'] ?? null);
        $this->response->success($this->posts->getRandom($count));
    }

    public function parseCount($input): int {
        $count = is_numeric($input) ? (int) $input : self::DEFAULT_COUNT;
        return max(1, min($count, self::MAX_COUNT));
    }

    public static function url(): string {
        return add_query_arg('action', self::ACTION, admin_url('admin-ajax.php'));
    }
}