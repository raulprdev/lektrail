<?php

namespace LekTrail\Tests\Mocks;

use LekTrail\Contracts\Hooks;

class MockHooks implements Hooks
{
    public array $actions = [];
    public array $shortcodes = [];

    public function addAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        $this->actions[$hook] = [
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $acceptedArgs,
        ];
    }

    public function addShortcode(string $tag, callable $callback): void
    {
        $this->shortcodes[$tag] = $callback;
    }
}
