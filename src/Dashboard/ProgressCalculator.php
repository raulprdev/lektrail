<?php

namespace LekTrail\Dashboard;

use LekTrail\Contracts\PostCountRepository;

class ProgressCalculator
{
    public function calculate(ReadingHistory $history, PostCountRepository $counts, ReadingFilter $filter): ReadingStats
    {
        $filtered = $history->filter($filter);
        $totalPosts = $counts->getCount($filter);

        return ReadingStats::create($filtered, $totalPosts);
    }
}
