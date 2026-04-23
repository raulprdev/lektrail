<?php

namespace LekTrail\WordPress;

use LekTrail\Contracts\CategoryResolver as CategoryResolverContract;

class CategoryResolver implements CategoryResolverContract
{
    public function getSlugsWithDescendants(string $slug): array
    {
        $term = get_term_by('slug', $slug, 'category');
        if (!$term) {
            return [$slug];
        }

        $childIds = get_term_children($term->term_id, 'category');
        if (empty($childIds) || is_wp_error($childIds)) {
            return [$slug];
        }

        $slugs = [$slug];
        foreach ($childIds as $childId) {
            $child = get_term($childId, 'category');
            if ($child && !is_wp_error($child)) {
                $slugs[] = $child->slug;
            }
        }

        return $slugs;
    }
}
