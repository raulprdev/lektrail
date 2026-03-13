<?php

namespace Completionist\Contracts;

interface TrackingRepository {
    public function track(int $userId, int $postId, string $status): void;
    public function getViewedIds(int $userId): array;
    public function getReadIds(int $userId): array;
    public function getViewedPosts(int $userId, int $limit): array;
    public function getReadPosts(int $userId, int $limit): array;
    public function clearHistory(int $userId): void;
}