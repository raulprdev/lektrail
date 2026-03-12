<?php

namespace Completionist\Tests\Mocks;

use Completionist\Contracts\JsonResponse;

class MockJsonResponse implements JsonResponse {

    public ?array $successData = null;
    public ?string $errorMessage = null;
    public ?int $errorCode = null;

    public function success(array $data): void {
        $this->successData = $data;
    }

    public function error(string $message, int $code = 400): void {
        $this->errorMessage = $message;
        $this->errorCode = $code;
    }
}