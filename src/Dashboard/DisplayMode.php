<?php

namespace LekTrail\Dashboard;

class DisplayMode
{
    public const PROGRESS = 'progress';
    public const REMAINING = 'remaining';
    public const COUNT = 'count';

    private const VALID = [self::PROGRESS, self::REMAINING, self::COUNT];

    private string $value;

    public function __construct(string $value = self::PROGRESS)
    {
        $this->value = in_array($value, self::VALID, true) ? $value : self::PROGRESS;
    }

    public static function fromArray(array $atts): self
    {
        $mode = isset($atts['mode']) ? trim((string) $atts['mode']) : self::PROGRESS;
        return new self($mode);
    }

    public function isProgress(): bool
    {
        return $this->value === self::PROGRESS;
    }

    public function isRemaining(): bool
    {
        return $this->value === self::REMAINING;
    }

    public function isCount(): bool
    {
        return $this->value === self::COUNT;
    }
}
