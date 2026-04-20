<?php

namespace LekTrail\Dashboard;

use LekTrail\Contracts\Hooks;
use LekTrail\Contracts\PostCountRepository;

class CacheInvalidator
{
    private PostCountRepository $counts;

    public function __construct(PostCountRepository $counts)
    {
        $this->counts = $counts;
    }

    public function register(Hooks $hooks): void
    {
        $hooks->addAction('transition_post_status', [$this, 'onTransition']);
    }

    public function onTransition(string $newStatus, string $oldStatus): void
    {
        if ($newStatus === 'publish' || $oldStatus === 'publish') {
            $this->counts->clearCache();
        }
    }
}
