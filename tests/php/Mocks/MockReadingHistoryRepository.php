<?php

namespace LekTrail\Tests\Mocks;

use LekTrail\Contracts\ReadingHistoryRepository;

class MockReadingHistoryRepository implements ReadingHistoryRepository
{
    public array $records = [];

    public function getAllForUser(int $userId): array
    {
        return $this->records;
    }
}
