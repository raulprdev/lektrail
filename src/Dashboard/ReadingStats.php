<?php

namespace LekTrail\Dashboard;

class ReadingStats
{
    private int $total;
    private int $read;
    private int $viewed;
    private float $percentage;

    private function __construct(int $total, int $read, int $viewed, float $percentage)
    {
        $this->total = $total;
        $this->read = $read;
        $this->viewed = $viewed;
        $this->percentage = $percentage;
    }

    public static function create(ReadingHistory $history, int $totalPosts): self
    {
        $read = $history->readCount();
        $viewed = $history->viewedCount();
        $percentage = $totalPosts > 0 ? round(($read / $totalPosts) * 100) : 0.0;

        return new self($totalPosts, $read, $viewed, $percentage);
    }

    public function total(): int
    {
        return $this->total;
    }

    public function read(): int
    {
        return $this->read;
    }

    public function viewed(): int
    {
        return $this->viewed;
    }

    public function percentage(): float
    {
        return $this->percentage;
    }
}
