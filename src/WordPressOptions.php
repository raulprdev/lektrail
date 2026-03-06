<?php

namespace Completionist;

class WordPressOptions implements Options {

    public function get(string $key, $default = null) {
        return get_option($key, $default);
    }

    public function set(string $key, $value): void {
        update_option($key, $value);
    }
}