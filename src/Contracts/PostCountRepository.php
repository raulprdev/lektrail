<?php

namespace LekTrail\Contracts;

use LekTrail\Dashboard\ReadingFilter;

interface PostCountRepository
{
    public function getCount(ReadingFilter $filter): int;

    public function clearCache(): void;
}
