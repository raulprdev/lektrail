<?php

namespace Completionist\Tests\Mocks;

use Completionist\Contracts\Hooks;

class MockHooks implements Hooks
{
    public array $actions = [];
    public array $shortcodes = [];

    public function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        $this->actions[$hook] = compact('callback', 'priority');
    }

    public function addShortcode(string $tag, callable $callback): void
    {
        $this->shortcodes[$tag] = $callback;
    }
}
