<?php

namespace Completionist;

interface Context {
    public function isSingular(array $postTypes): bool;
    public function getPostId(): int;
}