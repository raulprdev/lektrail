<?php

namespace LekTrail\WordPress;

use LekTrail\Contracts\Transients as TransientsContract;

class Transients implements TransientsContract
{
    public function get(string $key)
    {
        return get_transient($key);
    }

    public function set(string $key, $value, int $expiration): void
    {
        set_transient($key, $value, $expiration);
    }

    public function delete(string $key): void
    {
        delete_transient($key);
    }
}
