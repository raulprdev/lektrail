<?php

namespace Completionist;

class WordPressJsonResponse implements JsonResponse {

    public function success(array $data): void {
        wp_send_json_success($data);
    }

    public function error(string $message, int $code = 400): void {
        wp_send_json_error(['message' => $message], $code);
    }
}