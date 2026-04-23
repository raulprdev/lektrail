<?php

namespace LekTrail\Contracts;

interface CategoryResolver
{
    /** @return string[] Parent slug + all descendant slugs */
    public function getSlugsWithDescendants(string $slug): array;
}
