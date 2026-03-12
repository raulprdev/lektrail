<?php

namespace Completionist\WordPress;

use Completionist\Contracts\Options as OptionsInterface;

class Options implements OptionsInterface {

    public function get(string $key, $default = null) {
        return get_option($key, $default);
    }

    public function set(string $key, $value): void {
        update_option($key, $value);
    }
}