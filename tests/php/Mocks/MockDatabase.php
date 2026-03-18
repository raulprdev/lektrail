<?php

namespace Completionist\Tests\Mocks;

use Completionist\Contracts\Database;

class MockDatabase implements Database
{
    public array $queries = [];
    public array $results = [];
    public array $deletes = [];
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
}
