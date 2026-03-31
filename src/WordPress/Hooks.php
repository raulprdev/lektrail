<?php

namespace LekTrail\WordPress;

defined('ABSPATH') || exit;

use LekTrail\Contracts\Hooks as HooksInterface;

class Hooks implements HooksInterface
{
    public function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        add_action($hook, $callback, $priority);
    }

    public function addShortcode(string $tag, callable $callback): void
    {
        add_shortcode($tag, $callback);
    }
}
