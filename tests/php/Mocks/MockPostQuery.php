<?php

namespace Completionist\Tests\Mocks;

use Completionist\PostQuery;

class MockPostQuery implements PostQuery {

    public array $posts = [];

    public function getRandom(int $count): array {
        return array_slice($this->posts, 0, $count);
    }

    public function getTotalCount(): int {
        return count($this->posts);
    }
}