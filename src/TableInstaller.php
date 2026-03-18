<?php

namespace Completionist;

use Completionist\Contracts\Database;

class TableInstaller
{
    private const TABLE_NAME = 'completionist_history';

    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getTableName(): string
    {
        return $this->db->getPrefix() . self::TABLE_NAME;
    }

    public function createTable(): void
    {
        $table = $this->getTableName();
        $charset = $this->db->getCharsetCollate();
        $sql = "CREATE TABLE {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            status VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_post (user_id, post_id),
            KEY user_id (user_id)
        ) {$charset};";
        $this->db->createTable($sql);
    }

    public function dropTable(): void
    {
        $this->db->dropTable($this->getTableName());
    }
}
