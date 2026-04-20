<?php

namespace LekTrail\Dashboard;

use LekTrail\Contracts\Database;
use LekTrail\Contracts\ReadingHistoryRepository as ReadingHistoryRepositoryContract;

class ReadingHistoryRepository implements ReadingHistoryRepositoryContract
{
    private Database $db;
    private string $table;
    private ?array $cache = null;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = $db->getPrefix() . 'lektrail_history';
    }

    public function getAllForUser(int $userId): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $prefix = $this->db->getPrefix();
        $results = $this->db->getResults(
            "SELECT h.post_id, h.status, YEAR(p.post_date) as year,
                    GROUP_CONCAT(t.slug SEPARATOR ',') as slugs
             FROM {$this->table} h
             INNER JOIN {$prefix}posts p ON h.post_id = p.ID
             LEFT JOIN {$prefix}term_relationships tr ON p.ID = tr.object_id
             LEFT JOIN {$prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'category'
             LEFT JOIN {$prefix}terms t ON tt.term_id = t.term_id
             WHERE h.user_id = %d
             GROUP BY h.post_id, h.status, p.post_date
             ORDER BY h.created_at DESC",
            $userId
        );

        $this->cache = array_map(function (object $row): array {
            $slugs = $row->slugs ? explode(',', $row->slugs) : [];
            return [
                'post_id' => (int) $row->post_id,
                'status' => $row->status,
                'category_slugs' => $slugs,
                'year' => (int) $row->year,
            ];
        }, $results);

        return $this->cache;
    }
}
