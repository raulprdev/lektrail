<?php

namespace LekTrail\Tests\Mocks;

use LekTrail\Contracts\Database;

class MockDatabase implements Database
{
    public array $queries = [];
    public array $results = [];
    public array $deletes = [];
    public array $tables = [];
    public array $droppedTables = [];
    public string $prefix = 'wp_';

    public function query(string $sql, ...$args): bool
    {
        $this->queries[] = ['sql' => $sql, 'args' => $args];
        return true;
    }

    public function getResults(string $query, ...$args): array
    {
        $this->queries[] = ['sql' => $query, 'args' => $args];
        return array_shift($this->results) ?? [];
    }

    public function delete(string $table, array $where): bool
    {
        $this->deletes[] = ['table' => $table, 'where' => $where];
        return true;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getCharsetCollate(): string
    {
        return 'utf8mb4_unicode_ci';
    }

    public function createTable(string $sql): void
    {
        $this->tables[] = $sql;
    }

    public function dropTable(string $tableName): void
    {
        $this->droppedTables[] = $tableName;
    }
}
