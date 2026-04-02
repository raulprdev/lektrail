<?php

namespace LekTrail\WordPress;

use LekTrail\Contracts\Database as DatabaseContract;

class Database implements DatabaseContract
{
    public function query(string $sql, ...$args): bool
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql contains placeholders, args are passed to prepare()
        $preparedSql = $wpdb->prepare($sql, ...$args);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Custom table, SQL is prepared above
        return $wpdb->query($preparedSql) !== false;
    }

    public function getResults(string $query, ...$args): array
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $query contains placeholders, args are passed to prepare()
        $preparedQuery = $wpdb->prepare($query, ...$args);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Custom table, SQL is prepared above
        return $wpdb->get_results($preparedQuery, ARRAY_A) ?: [];
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

    public function getCharsetCollate(): string
    {
        global $wpdb;
        return $wpdb->get_charset_collate();
    }

    public function createTable(string $sql): void
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function dropTable(string $tableName): void
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- DDL statement, table name is internal constant
        $wpdb->query("DROP TABLE IF EXISTS {$tableName}");
    }
}
