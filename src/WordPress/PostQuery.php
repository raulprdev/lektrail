<?php

namespace Completionist\WordPress;

use Completionist\Contracts\PostQuery as PostQueryInterface;

class PostQuery implements PostQueryInterface
{
    public function query(array $args): array
    {
        $args['fields'] = 'ids';
        $query = new \WP_Query($args);

        $posts = [];
        foreach ($query->posts as $postId) {
            $posts[] = $this->formatPost($postId);
        }

        return $posts;
    }

    public function getRandom(int $count): array
    {
        return $this->query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $count,
            'orderby' => 'rand',
        ]);
    }

    public function getRecent(int $count): array
    {
        return $this->query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $count,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
    }

    private function formatPost(int $postId): array
    {
        $post = [
            'id' => $postId,
            'title' => get_the_title($postId),
            'url' => get_permalink($postId),
        ];

        $excerpt = get_the_excerpt($postId);
        if ($excerpt) {
            $post['excerpt'] = wp_strip_all_tags($excerpt);
        }

        $thumbnailId = get_post_thumbnail_id($postId);
        if ($thumbnailId) {
            $thumbnail = wp_get_attachment_image_url($thumbnailId, 'thumbnail');
            if ($thumbnail) {
                $post['thumbnail'] = $thumbnail;
            }
        }

        return $post;
    }

    public function getTotalCount(): int
    {
        return (int) wp_count_posts('post')->publish;
    }

    public function getPostData(int $postId): array
    {
        return $this->formatPost($postId);
    }

    public function getPostsDataByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        return $this->query([
            'post_type' => 'any',
            'post_status' => 'publish',
            'post__in' => $ids,
            'orderby' => 'post__in',
            'posts_per_page' => count($ids),
        ]);
    }
}
