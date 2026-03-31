<?php

namespace LekTrail;

use LekTrail\Contracts\PluginConfigRepository;
use LekTrail\Contracts\TrackingRepository;
use LekTrail\Contracts\UserProvider;

class TrackingService
{
    private UserProvider $userProvider;
    private TrackingRepository $trackings;
    private PluginConfigRepository $pluginConfigs;

    public function __construct(UserProvider $userProvider, TrackingRepository $trackings, PluginConfigRepository $pluginConfigs)
    {
        $this->userProvider = $userProvider;
        $this->trackings    = $trackings;
        $this->pluginConfigs = $pluginConfigs;
    }

    public function trackViewed(int $postId): void
    {
        if (!$this->shouldTrackServerSide()) {
            return;
        }
        $this->trackings->track($this->userProvider->getCurrentUserId(), $postId, 'viewed');
    }

    public function trackRead(int $postId): void
    {
        if (!$this->shouldTrackServerSide()) {
            return;
        }
        $this->trackings->track($this->userProvider->getCurrentUserId(), $postId, 'read');
    }

    public function shouldTrackServerSide(): bool
    {
        return $this->pluginConfigs->load()->trackLoggedInUsers() && $this->userProvider->isLoggedIn();
    }

    public function getHistory(): array
    {
        if (!$this->shouldTrackServerSide()) {
            return ['viewed' => [], 'read' => []];
        }
        $userId = $this->userProvider->getCurrentUserId();
        $pluginConfig = $this->pluginConfigs->load();
        return [
            'viewed' => $this->trackings->getViewedPosts($userId, $pluginConfig->maxViewed()),
            'read' => $this->trackings->getReadPosts($userId, $pluginConfig->maxRead()),
        ];
    }
}
