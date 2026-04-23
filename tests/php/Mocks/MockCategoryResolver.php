<?php

namespace LekTrail\Tests\Mocks;

use LekTrail\Contracts\CategoryResolver;

class MockCategoryResolver implements CategoryResolver
{
    public array $descendants = [];

    public function getSlugsWithDescendants(string $slug): array
    {
        return $this->descendants[$slug] ?? [$slug];
    }
}
