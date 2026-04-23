<?php

namespace LekTrail\Blocks;

use LekTrail\Contracts\Hooks;

class Patterns
{
    public function register(Hooks $hooks): void
    {
        $hooks->addAction('init', [$this, 'registerPatterns']);
    }

    public function registerPatterns(): void
    {
        register_block_pattern_category('lektrail', [
            'label' => 'LekTrail',
        ]);

        register_block_pattern('lektrail/category-breakdown', [
            'title' => __('Category Breakdown', 'lektrail-reading-tracker'),
            'description' => __('Three progress blocks side by side, one per category.', 'lektrail-reading-tracker'),
            'categories' => ['lektrail'],
            'content' => $this->categoryBreakdown(),
        ]);

        register_block_pattern('lektrail/year-overview', [
            'title' => __('Year Overview', 'lektrail-reading-tracker'),
            'description' => __('Progress bar for a year with a reading list below.', 'lektrail-reading-tracker'),
            'categories' => ['lektrail'],
            'content' => $this->yearOverview(),
        ]);

        register_block_pattern('lektrail/simple-dashboard', [
            'title' => __('Simple Dashboard', 'lektrail-reading-tracker'),
            'description' => __('A progress bar with a reading list below.', 'lektrail-reading-tracker'),
            'categories' => ['lektrail'],
            'content' => $this->simpleDashboard(),
        ]);
    }

    private function categoryBreakdown(): string
    {
        return '<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Category 1</h3>
<!-- /wp:heading -->
<!-- wp:lektrail/progress {"mode":"progress"} /-->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Category 2</h3>
<!-- /wp:heading -->
<!-- wp:lektrail/progress {"mode":"remaining"} /-->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Category 3</h3>
<!-- /wp:heading -->
<!-- wp:lektrail/progress {"mode":"count"} /-->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->';
    }

    private function yearOverview(): string
    {
        return '<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">2024</h3>
<!-- /wp:heading -->
<!-- wp:lektrail/progress {"year":2024,"mode":"progress"} /-->
<!-- wp:lektrail/reading-list {"year":2024} /-->';
    }

    private function simpleDashboard(): string
    {
        return '<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">My Reading Progress</h3>
<!-- /wp:heading -->
<!-- wp:lektrail/progress {"mode":"progress"} /-->
<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Posts Read</h3>
<!-- /wp:heading -->
<!-- wp:lektrail/reading-list /-->';
    }
}
