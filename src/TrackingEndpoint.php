<?php

namespace Completionist;

use Completionist\Contracts\Hooks;
use Completionist\Contracts\JsonResponse;
use Completionist\Contracts\NonceVerifier;

class TrackingEndpoint
{
    public const ACTION = 'completionist_track_read';

    private TrackingService $trackingService;
    private JsonResponse $jsonResponse;
    private NonceVerifier $nonceVerifier;

    public function __construct(
        TrackingService $trackingService,
        JsonResponse $jsonResponse,
        NonceVerifier $nonceVerifier
    ) {
        $this->trackingService = $trackingService;
        $this->jsonResponse    = $jsonResponse;
        $this->nonceVerifier   = $nonceVerifier;
    }

    public function register(Hooks $hooks): void
    {
        $hooks->addAction('wp_ajax_' . self::ACTION, [$this, 'handle']);
    }

    public function handle(): void
    {
        $nonce = $_POST['nonce'] ?? '';
        if ($nonce === '' || !$this->nonceVerifier->verify($nonce, self::ACTION)) {
            $this->jsonResponse->error('Invalid nonce', 403);
            return;
        }

        if (!$this->trackingService->shouldTrackServerSide()) {
            $this->jsonResponse->error('Not logged in', 401);
            return;
        }

        $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        if (!$postId) {
            $this->jsonResponse->error('Missing post_id', 400);
            return;
        }

        $this->trackingService->trackRead($postId);
        $this->jsonResponse->success(['tracked' => true]);
    }

    public static function url(): string
    {
        return add_query_arg('action', self::ACTION, admin_url('admin-ajax.php'));
    }
}
