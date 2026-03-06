<?php

namespace Completionist\Tests\Mocks;

use Completionist\Options;

class MockOptions implements Options {

    private array $data = [];

    public function get(string $key, $default = null) {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $value): void {
        $this->data[$key] = $value;
    }
}