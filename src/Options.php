<?php

namespace Completionist;

interface Options {
    public function get(string $key, $default = null);
    public function set(string $key, $value): void;
}