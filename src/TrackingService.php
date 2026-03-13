<?php

namespace Completionist;

use Completionist\Contracts\SettingsRepository;
use Completionist\Contracts\TrackingRepository;
use Completionist\Contracts\UserProvider;

class TrackingService
{
    private UserProvider $userProvider;
    private TrackingRepository $trackings;
    private SettingsRepository $settings;

    public function __construct(UserProvider $userProvider, TrackingRepository $trackings, SettingsRepository $settings)
    {
        $this->userProvider = $userProvider;
        $this->trackings    = $trackings;
        $this->settings = $settings;
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
        return $this->settings->load()->trackLoggedInUsers() && $this->userProvider->isLoggedIn();
    }

    public function getHistory(): array
    {
        if (!$this->shouldTrackServerSide()) {
            return ['viewed' => [], 'read' => []];
        }
        $userId = $this->userProvider->getCurrentUserId();
        $settings = $this->settings->load();
        return [
            'viewed' => $this->trackings->getViewedPosts($userId, $settings->maxViewed()),
            'read' => $this->trackings->getReadPosts($userId, $settings->maxRead()),
        ];
    }
}
