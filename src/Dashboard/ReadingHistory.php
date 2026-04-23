<?php

namespace LekTrail\Dashboard;

class ReadingHistory
{
    private array $records;

    public function __construct(array $records)
    {
        $this->records = $records;
    }

    public function filter(ReadingFilter $filter): self
    {
        $filtered = $this->records;

        if ($filter->categorySlug() !== null) {
            $slug = $filter->categorySlug();
            $filtered = array_filter($filtered, fn (array $r) => in_array($slug, $r['category_slugs'], true));
        }

        if ($filter->year() !== null) {
            $year = $filter->year();
            $filtered = array_filter($filtered, fn (array $r) => $r['year'] === $year);
        }

        if ($filter->status() !== null) {
            $status = $filter->status();
            $filtered = array_filter($filtered, fn (array $r) => $r['status'] === $status);
        }

        return new self(array_values($filtered));
    }

    public function readCount(): int
    {
        return count(array_filter($this->records, fn (array $r) => $r['status'] === 'read'));
    }

    public function viewedCount(): int
    {
        return count(array_filter($this->records, fn (array $r) => $r['status'] === 'viewed'));
    }

    public function postIds(): array
    {
        return array_map(fn (array $r) => $r['post_id'], $this->records);
    }

    /** @return array<int, string> post_id => status */
    public function statusMap(): array
    {
        $map = [];
        foreach ($this->records as $r) {
            $map[$r['post_id']] = $r['status'];
        }
        return $map;
    }
}
