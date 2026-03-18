<?php

namespace Completionist\WordPress;

use Completionist\Contracts\Database;
use Completionist\Contracts\TrackingRepository as TrackingRepositoryContract;

class TrackingRepository implements TrackingRepositoryContract
{
    private Database $db;
    private string $table;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->getPrefix() . 'completionist_history';
    }

    public function track(int $userId, int $postId, string $status): void
    {
        $this->db->query(
            "INSERT INTO {$this->table} (user_id, post_id, status, created_at) VALUES (%d, %d, %s, NOW())
             ON DUPLICATE KEY UPDATE status = %s, created_at = NOW()",
            $userId,
            $postId,
            $status,
            $status
        );
    }

    public function getViewedIds(int $userId): array
    {
        return $this->getIdsByStatus($userId, 'viewed');
    }

    public function getReadIds(int $userId): array
    {
        return $this->getIdsByStatus($userId, 'read');
    }

    public function getViewedPosts(int $userId, int $limit): array
    {
        return $this->getPostsByStatus($userId, 'viewed', $limit);
    }

    public function getReadPosts(int $userId, int $limit): array
    {
        return $this->getPostsByStatus($userId, 'read', $limit);
    }

    public function clearHistory(int $userId): void
    {
        $this->db->delete($this->table, ['user_id' => $userId]);
    }

    private function getIdsByStatus(int $userId, string $status): array
    {
        $results = $this->db->getResults(
            "SELECT post_id FROM {$this->table} WHERE user_id = %d AND status = %s ORDER BY created_at DESC",
            $userId,
            $status
        );
        return array_column($results, 'post_id');
    }

    private function getPostsByStatus(int $userId, string $status, int $limit): array
    {
        return $this->db->getResults(
            "SELECT post_id as id FROM {$this->table} WHERE user_id = %d AND status = %s ORDER BY created_at DESC LIMIT %d",
            $userId,
            $status,
            $limit
        );
    }
}