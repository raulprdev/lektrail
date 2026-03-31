<?php

namespace LekTrail\Contracts;

interface JsonResponse
{
    public function success(array $data): void;
    public function error(string $message, int $code): void;
}
