<?php

namespace Completionist\Contracts;

interface Database
{
    public function query(string $sql, ...$args): bool;
    public function getResults(string $query, ...$args): array;
    public function delete(string $table, array $where): bool;
    public function getPrefix(): string;
    public function getCharsetCollate(): string;
    public function createTable(string $sql): void;
    public function dropTable(string $tableName): void;
}
