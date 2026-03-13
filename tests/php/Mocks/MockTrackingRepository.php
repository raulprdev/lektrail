<?php

namespace Completionist\Tests\Mocks;

use Completionist\Contracts\TrackingRepository;

class MockTrackingRepository implements TrackingRepository {

    public array $history = [];

    public function track(int $userId, int $postId, string $status): void {
        if (!isset($this->history[$userId])) {
            $this->history[$userId] = [];
        }
        $this->history[$userId][$postId] = $status;
    }

    public function getViewedIds(int $userId): array {
        return $this->getIdsByStatus($userId, 'viewed');
    }

    public function getReadIds(int $userId): array {
        return $this->getIdsByStatus($userId, 'read');
    }

    public function getViewedPosts(int $userId, int $limit): array {
        $ids = array_slice($this->getViewedIds($userId), 0, $limit);
        return array_map(fn($id) => ['id' => $id], $ids);
    }

    public function getReadPosts(int $userId, int $limit): array {
        $ids = array_slice($this->getReadIds($userId), 0, $limit);
        return array_map(fn($id) => ['id' => $id], $ids);
    }

    public function clearHistory(int $userId): void {
        unset($this->history[$userId]);
    }

    private function getIdsByStatus(int $userId, string $status): array {
        if (!isset($this->history[$userId])) {
            return [];
        }
        $ids = [];
        foreach ($this->history[$userId] as $postId => $postStatus) {
            if ($postStatus === $status) {
                $ids[] = $postId;
            }
        }
        return $ids;
    }
}