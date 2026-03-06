<?php

namespace Completionist;

class WordPressContext implements Context {

    public function isSingular(array $postTypes): bool {
        return is_singular($postTypes);
    }

    public function getPostId(): int {
        return (int) get_the_ID();
    }
}