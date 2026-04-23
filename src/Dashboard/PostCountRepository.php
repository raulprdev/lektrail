<?php

namespace LekTrail\Dashboard;

use LekTrail\Contracts\Database;
use LekTrail\Contracts\PostCountRepository as PostCountRepositoryContract;
use LekTrail\Contracts\Transients;

class PostCountRepository implements PostCountRepositoryContract
{
    private const TRANSIENT_KEY = 'lektrail_post_counts';
    private const EXPIRATION = 86400;

    private Database $db;
    private Transients $transients;

    public function __construct(Database $db, Transients $transients)
    {
        $this->db = $db;
        $this->transients = $transients;
    }

    public function getCount(ReadingFilter $filter): int
    {
        $cached = $this->transients->get(self::TRANSIENT_KEY) ?: [];
        $key = $filter->cacheKey();

        if (isset($cached[$key])) {
            return $cached[$key];
        }

        $count = $this->queryCount($filter);
        $cached[$key] = $count;
        $this->transients->set(self::TRANSIENT_KEY, $cached, self::EXPIRATION);

        return $count;
    }

    public function clearCache(): void
    {
        $this->transients->delete(self::TRANSIENT_KEY);
    }

    private function queryCount(ReadingFilter $filter): int
    {
        $prefix = $this->db->getPrefix();
        $where = "p.post_type = 'post' AND p.post_status = 'publish'";
        $joins = '';
        $args = [];

        if ($filter->categorySlugs() !== null) {
            $slugs = $filter->categorySlugs();
            $joins .= " INNER JOIN {$prefix}term_relationships tr ON p.ID = tr.object_id"
                     . " INNER JOIN {$prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'category'"
                     . " INNER JOIN {$prefix}terms t ON tt.term_id = t.term_id";
            $placeholders = implode(', ', array_fill(0, count($slugs), '%s'));
            $where .= " AND t.slug IN ({$placeholders})";
            array_push($args, ...$slugs);
        }

        if ($filter->year() !== null) {
            $where .= ' AND YEAR(p.post_date) = %d';
            $args[] = $filter->year();
        }

        $sql = "SELECT COUNT(DISTINCT p.ID) as cnt FROM {$prefix}posts p{$joins} WHERE {$where}";
        $results = $this->db->getResults($sql, ...$args);

        return (int) ($results[0]['cnt'] ?? 0);
    }
}
