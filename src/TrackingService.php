<?php

namespace Completionist;

use Completionist\Contracts\SettingsRepository;
use Completionist\Contracts\UserProvider;
use Completionist\Contracts\TrackingRepository;

class TrackingService {

    private UserProvider $users;
    private TrackingRepository $tracking;
    private SettingsRepository $settings;

    public function __construct(UserProvider $users, TrackingRepository $tracking, SettingsRepository $settings) {
        $this->users = $users;
        $this->tracking = $tracking;
        $this->settings = $settings;
    }

    public function trackViewed(int $postId): void {
        if (!$this->shouldTrackServerSide()) {
            return;
        }
        $this->tracking->track($this->users->getCurrentUserId(), $postId, 'viewed');
    }

    public function trackRead(int $postId): void {
        if (!$this->shouldTrackServerSide()) {
            return;
        }
        $this->tracking->track($this->users->getCurrentUserId(), $postId, 'read');
    }

    public function shouldTrackServerSide(): bool {
        return $this->settings->load()->trackLoggedInUsers() && $this->users->isLoggedIn();
    }

    public function getHistory(): array {
        if (!$this->shouldTrackServerSide()) {
            return ['viewed' => [], 'read' => []];
        }
        $userId = $this->users->getCurrentUserId();
        $settings = $this->settings->load();
        return [
            'viewed' => $this->tracking->getViewedPosts($userId, $settings->maxViewed()),
            'read' => $this->tracking->getReadPosts($userId, $settings->maxRead()),
        ];
    }
}