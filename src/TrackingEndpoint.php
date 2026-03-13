<?php

namespace Completionist;

use Completionist\Contracts\Hooks;
use Completionist\Contracts\JsonResponse;

class TrackingEndpoint {

    public const ACTION = 'completionist_track_read';

    private TrackingService $tracking;
    private JsonResponse $response;

    public function __construct(TrackingService $tracking, JsonResponse $response) {
        $this->tracking = $tracking;
        $this->response = $response;
    }

    public function register(Hooks $hooks): void {
        $hooks->addAction('wp_ajax_' . self::ACTION, [$this, 'handle']);
    }

    public function handle(): void {
        if (!$this->tracking->shouldTrackServerSide()) {
            $this->response->error('Not logged in');
            return;
        }

        $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        if (!$postId) {
            $this->response->error('Missing post_id');
            return;
        }

        $this->tracking->trackRead($postId);
        $this->response->success(['tracked' => true]);
    }

    public static function url(): string {
        return add_query_arg('action', self::ACTION, admin_url('admin-ajax.php'));
    }
}