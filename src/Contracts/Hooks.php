<?php

namespace LekTrail\Contracts;

interface Hooks
{
    public function addAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void;
    public function addShortcode(string $tag, callable $callback): void;
}
