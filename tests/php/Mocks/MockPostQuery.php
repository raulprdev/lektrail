<?php

namespace Completionist\Tests\Mocks;

use Completionist\Contracts\PostQuery;

class MockPostQuery implements PostQuery
{
    public array $posts = [];
    public array $postData = [];
    public ?array $lastQueryArgs = null;
    public ?int $lastRecentLimit = null;

    public function query(array $args): array
    {
        $this->lastQueryArgs = $args;
        $posts = $this->posts;

        if (!empty($args['post__not_in'])) {
            $posts = array_filter($posts, function ($post) use ($args) {
                return !in_array($post['id'], $args['post__not_in']);
            });
        }

        $limit = $args['posts_per_page'] ?? count($posts);
        return array_slice(array_values($posts), 0, $limit);
    }

    public function getRandom(int $count): array
    {
        return array_slice($this->posts, 0, $count);
    }

    public function getRecent(int $count): array
    {
        $this->lastRecentLimit = $count;
        return array_slice($this->posts, 0, $count);
    }

    public function getTotalCount(): int
    {
        return count($this->posts);
    }

    public function getPostData(int $postId): array
    {
        return $this->postData[$postId] ?? ['id' => $postId];
    }

    public function getPostsDataByIds(array $ids): array
    {
        return array_map(fn ($id) => $this->getPostData($id), $ids);
    }
}
