<?php

namespace LekTrail\Contracts;

interface ReadingHistoryRepository
{
    /** @return array<array{post_id: int, status: string, category_slugs: string[], year: int}> */
    public function getAllForUser(int $userId): array;
}
