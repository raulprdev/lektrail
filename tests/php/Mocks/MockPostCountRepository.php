<?php

namespace LekTrail\Tests\Mocks;

use LekTrail\Contracts\PostCountRepository;
use LekTrail\Dashboard\ReadingFilter;

class MockPostCountRepository implements PostCountRepository
{
    public array $counts = [];
    public int $clearCacheCalls = 0;

    public function getCount(ReadingFilter $filter): int
    {
        return $this->counts[$filter->cacheKey()] ?? 0;
    }

    public function clearCache(): void
    {
        $this->clearCacheCalls++;
    }
}
