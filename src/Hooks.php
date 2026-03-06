<?php

namespace Completionist;

interface Hooks {
    public function addAction(string $hook, callable $callback, int $priority = 10): void;
    public function addShortcode(string $tag, callable $callback): void;
}