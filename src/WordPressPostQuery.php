<?php

namespace Completionist;

class WordPressPostQuery implements PostQuery {

    public function getRandom(int $count): array {
        $query = new \WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $count,
            'orderby' => 'rand',
            'fields' => 'ids',
        ]);

        $posts = [];
        foreach ($query->posts as $postId) {
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

            $posts[] = $post;
        }

        return $posts;
    }

    public function getTotalCount(): int {
        return (int) wp_count_posts('post')->publish;
    }

    public function getPostData(int $postId): array {
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
}