<?php

namespace Completionist\WordPress;

use Completionist\Contracts\Database as DatabaseContract;

class Database implements DatabaseContract
{
    public function query(string $sql, ...$args): bool
    {
        global $wpdb;
        $prepared = empty($args) ? $sql : $wpdb->prepare($sql, ...$args);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table
        return $wpdb->query($prepared) !== false;
    }

    public function getResults(string $query, ...$args): array
    {
        global $wpdb;
        $prepared = empty($args) ? $query : $wpdb->prepare($query, ...$args);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table
        return $wpdb->get_results($prepared, ARRAY_A) ?: [];
    }

    public function delete(string $table, array $where): bool
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table
        return $wpdb->delete($table, $where) !== false;
    }

    public function getPrefix(): string
    {
        global $wpdb;
        return $wpdb->prefix;
    }
}