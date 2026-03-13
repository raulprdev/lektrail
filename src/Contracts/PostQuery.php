<?php

namespace Completionist\Contracts;

interface PostQuery {
    public function query(array $args): array;
    public function getRandom(int $count): array;
    public function getTotalCount(): int;
    public function getPostData(int $postId): array;
    public function getPostsDataByIds(array $ids): array;
}