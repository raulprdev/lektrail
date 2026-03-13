<?php

namespace Completionist;

use Completionist\Contracts\Database;

class TableInstaller {

    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function createTable(): void {
        $table = $this->db->getPrefix() . 'completionist_history';
        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            status VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_post (user_id, post_id),
            KEY user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->db->query($sql);
    }

    public function dropTable(): void {
        $table = $this->db->getPrefix() . 'completionist_history';
        $this->db->query("DROP TABLE IF EXISTS {$table}");
    }
}