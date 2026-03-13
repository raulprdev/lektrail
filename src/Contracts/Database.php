<?php

namespace Completionist\Contracts;

interface Database
{
    public function query(string $sql): bool;
    public function getResults(string $query): array;
    public function delete(string $table, array $where): bool;
    public function getPrefix(): string;
}
