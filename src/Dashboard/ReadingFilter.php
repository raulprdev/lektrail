<?php

namespace LekTrail\Dashboard;

class ReadingFilter
{
    private ?string $categorySlug;
    private ?int $year;
    private ?string $status;
    private ?array $resolvedCategorySlugs = null;

    public function __construct(?string $categorySlug = null, ?int $year = null, ?string $status = null)
    {
        $this->categorySlug = $categorySlug;
        $this->year = $year;
        $this->status = $status;
    }

    public static function fromArray(array $atts): self
    {
        $category = isset($atts['category']) ? trim((string) $atts['category']) : null;
        $year = isset($atts['year']) ? (int) $atts['year'] : null;
        $status = isset($atts['status']) ? trim((string) $atts['status']) : null;

        if ($category === '') {
            $category = null;
        }
        if ($year !== null && ($year < 1000 || $year > 9999)) {
            $year = null;
        }
        if ($status !== null && !in_array($status, ['viewed', 'read'], true)) {
            $status = null;
        }

        return new self($category, $year, $status);
    }

    public function categorySlug(): ?string
    {
        return $this->categorySlug;
    }

    public function year(): ?int
    {
        return $this->year;
    }

    public function status(): ?string
    {
        return $this->status;
    }

    /** @return string[]|null */
    public function categorySlugs(): ?array
    {
        if ($this->resolvedCategorySlugs !== null) {
            return $this->resolvedCategorySlugs;
        }

        return $this->categorySlug !== null ? [$this->categorySlug] : null;
    }

    /** @param string[] $slugs */
    public function withResolvedCategories(array $slugs): self
    {
        $copy = clone $this;
        $copy->resolvedCategorySlugs = $slugs;
        return $copy;
    }

    public function cacheKey(): string
    {
        $parts = [];

        if ($this->categorySlug !== null) {
            $parts[] = 'cat:' . $this->categorySlug;
        }
        if ($this->year !== null) {
            $parts[] = 'year:' . $this->year;
        }

        return empty($parts) ? '_global' : implode('|', $parts);
    }
}
