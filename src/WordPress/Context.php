<?php

namespace LekTrail\WordPress;

use LekTrail\Contracts\Context as ContextInterface;

class Context implements ContextInterface
{
    public function isSingular(array $postTypes): bool
    {
        return is_singular($postTypes);
    }

    public function getPostId(): int
    {
        return (int) get_the_ID();
    }
}
