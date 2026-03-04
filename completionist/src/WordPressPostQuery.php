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
            $posts[] = [
                'id' => $postId,
                'title' => get_the_title($postId),
                'url' => get_permalink($postId),
            ];
        }

        return $posts;
    }

    public function getTotalCount(): int {
        return (int) wp_count_posts('post')->publish;
    }
}