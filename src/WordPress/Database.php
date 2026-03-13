<?php

namespace Completionist\WordPress;

use Completionist\Contracts\Database as DatabaseContract;

class Database implements DatabaseContract
{
    public function query(string $sql): bool
    {
        global $wpdb;
        return $wpdb->query($sql) !== false;
    }

    public function getResults(string $query): array
    {
        global $wpdb;
        return $wpdb->get_results($query, ARRAY_A) ?: [];
    }

    public function delete(string $table, array $where): bool
    {
        global $wpdb;
        return $wpdb->delete($table, $where) !== false;
    }

    public function getPrefix(): string
    {
        global $wpdb;
        return $wpdb->prefix;
    }
}
