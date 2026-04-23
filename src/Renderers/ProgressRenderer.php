<?php

namespace LekTrail\Renderers;

use LekTrail\Assets;
use LekTrail\Contracts\CategoryResolver;
use LekTrail\Contracts\PostCountRepository;
use LekTrail\Contracts\ReadingHistoryRepository;
use LekTrail\Contracts\UserProvider;
use LekTrail\Dashboard\DisplayMode;
use LekTrail\Dashboard\ProgressCalculator;
use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Dashboard\ReadingHistory;
use LekTrail\Dashboard\ReadingStats;

class ProgressRenderer
{
    private ReadingHistoryRepository $history;
    private PostCountRepository $counts;
    private UserProvider $users;
    private Assets $assets;
    private CategoryResolver $categoryResolver;

    public function __construct(
        ReadingHistoryRepository $history,
        PostCountRepository $counts,
        UserProvider $users,
        Assets $assets,
        CategoryResolver $categoryResolver
    ) {
        $this->history = $history;
        $this->counts = $counts;
        $this->users = $users;
        $this->assets = $assets;
        $this->categoryResolver = $categoryResolver;
    }

    public function render(ReadingFilter $filter, string $wrapperAttributes = '', ?DisplayMode $mode = null): string
    {
        if (!$this->users->isLoggedIn()) {
            return '';
        }

        $mode = $mode ?? new DisplayMode();
        $filter = $this->resolveCategories($filter);
        $records = $this->history->getAllForUser($this->users->getCurrentUserId());
        $history = new ReadingHistory($records);
        $calculator = new ProgressCalculator();
        $stats = $calculator->calculate($history, $this->counts, $filter);

        $this->assets->enqueueDashboardStyle();

        $percentage = (int) $stats->percentage();
        $fillWidth = max($percentage, $percentage > 0 ? 2 : 0);
        $hero = $this->buildHero($mode, $stats, $percentage);
        $detail = $this->buildDetail($mode, $stats);
        $wrapper = $wrapperAttributes ?: 'class="lektrail-progress"';

        return sprintf(
            '<div %s>'
            . '<div class="lektrail-progress__hero">%s</div>'
            . '<div class="lektrail-progress__detail">%s</div>'
            . '<div class="lektrail-progress__bar"><div class="lektrail-progress__fill" style="width:%d%%"></div></div>'
            . '</div>',
            $wrapper,
            $hero,
            esc_html($detail),
            $fillWidth
        );
    }

    private function buildHero(DisplayMode $mode, ReadingStats $stats, int $percentage): string
    {
        if ($mode->isRemaining()) {
            return (string) ($stats->total() - $stats->read());
        }
        if ($mode->isCount()) {
            return (string) $stats->read();
        }
        return $percentage . '%';
    }

    private function buildDetail(DisplayMode $mode, ReadingStats $stats): string
    {
        if ($mode->isProgress()) {
            /* translators: 1: number of posts read, 2: total posts */
            return sprintf(__('%1$d of %2$d posts read', 'lektrail-reading-tracker'), $stats->read(), $stats->total());
        }
        /* translators: 1: total posts */
        return sprintf(__('of %1$d posts', 'lektrail-reading-tracker'), $stats->total());
    }

    private function resolveCategories(ReadingFilter $filter): ReadingFilter
    {
        if ($filter->categorySlug() === null) {
            return $filter;
        }

        $slugs = $this->categoryResolver->getSlugsWithDescendants($filter->categorySlug());
        return $filter->withResolvedCategories($slugs);
    }
}
