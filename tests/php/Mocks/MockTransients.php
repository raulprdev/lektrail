<?php

namespace LekTrail\Tests\Mocks;

use LekTrail\Contracts\Transients;

class MockTransients implements Transients
{
    public array $store = [];

    public function get(string $key)
    {
        return $this->store[$key] ?? false;
    }

    public function set(string $key, $value, int $expiration): void
    {
        $this->store[$key] = $value;
    }

    public function delete(string $key): void
    {
        unset($this->store[$key]);
    }
}
