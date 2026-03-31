<?php

namespace LekTrail\Tests\Mocks;

use LekTrail\Contracts\Context;

class MockContext implements Context
{
    public ?string $singularPostType = null;
    public int $postId = 0;

    public function isSingular(array $postTypes): bool
    {
        if ($this->singularPostType === null) {
            return false;
        }
        return in_array($this->singularPostType, $postTypes, true);
    }

    public function getPostId(): int
    {
        return $this->postId;
    }
}
