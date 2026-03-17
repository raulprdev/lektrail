<?php

namespace Completionist;

use Completionist\Contracts\Hooks;
use Completionist\Contracts\JsonResponse;

class SuggestionsEndpoint
{
    public const ACTION = 'completionist_suggestions';

    private SuggestionsQuery $suggestionsQuery;
    private JsonResponse $jsonResponse;

    public function __construct(SuggestionsQuery $suggestionsQuery, JsonResponse $jsonResponse)
    {
        $this->suggestionsQuery = $suggestionsQuery;
        $this->jsonResponse = $jsonResponse;
    }

    public function register(Hooks $hooks): void
    {
        $hooks->addAction('wp_ajax_' . self::ACTION, [$this, 'handle']);
        $hooks->addAction('wp_ajax_nopriv_' . self::ACTION, [$this, 'handle']);
    }

    public function handle(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public read-only endpoint
        $exclude = isset($_GET['exclude']) ? sanitize_text_field(wp_unslash($_GET['exclude'])) : '';
        $excludeIds = $this->parseExcludeIds($exclude);
        $this->jsonResponse->success($this->suggestionsQuery->get($excludeIds));
    }

    public function parseExcludeIds(string $input): array
    {
        if (empty($input)) {
            return [];
        }
        return array_values(array_filter(
            array_map('intval', explode(',', $input)),
            fn ($id) => $id > 0
        ));
    }

    public static function url(): string
    {
        return add_query_arg('action', self::ACTION, admin_url('admin-ajax.php'));
    }
}
