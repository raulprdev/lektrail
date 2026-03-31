<?php

namespace LekTrail\Contracts;

interface Context
{
    public function isSingular(array $postTypes): bool;
    public function getPostId(): int;
}
