<?php

namespace LekTrail\Contracts;

interface Transients
{
    public function get(string $key);

    public function set(string $key, $value, int $expiration): void;

    public function delete(string $key): void;
}
