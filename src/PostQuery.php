<?php

namespace Completionist;

interface PostQuery {
    public function getRandom(int $count): array;
    public function getTotalCount(): int;
}