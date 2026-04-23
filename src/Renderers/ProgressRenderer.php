<?php

namespace LekTrail\Renderers;

use LekTrail\Assets;
use LekTrail\Contracts\PostCountRepository;
use LekTrail\Contracts\ReadingHistoryRepository;
use LekTrail\Contracts\UserProvider;
use LekTrail\Dashboard\ProgressCalculator;
use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Dashboard\ReadingHistory;

class ProgressRenderer
{
    private ReadingHistoryRepository $history;
    private PostCountRepository $counts;
    private UserProvider $users;
    private Assets $assets;

    public function __construct(
        ReadingHistoryRepository $history,
        PostCountRepository $counts,
        UserProvider $users,
        Assets $assets
    ) {
        $this->history = $history;
        $this->counts = $counts;
        $this->users = $users;
        $this->assets = $assets;
    }

    public function render(ReadingFilter $filter, string $wrapperAttributes = ''): string
    {
        if (!$this->users->isLoggedIn()) {
            return '';
        }

        $records = $this->history->getAllForUser($this->users->getCurrentUserId());
        $history = new ReadingHistory($records);
        $calculator = new ProgressCalculator();
        $stats = $calculator->calculate($history, $this->counts, $filter);

        $this->assets->enqueueDashboardStyle();

        $percentage = (string) $stats->percentage();
        /* translators: 1: number of posts read, 2: total posts, 3: percentage */
        $label = sprintf(__('%1$d of %2$d posts read (%3$s%%)', 'lektrail-reading-tracker'), $stats->read(), $stats->total(), $percentage);

        if ($wrapperAttributes) {
            return sprintf(
                '<div %s><div class="lektrail-progress__label">%s</div>'
                . '<div class="lektrail-progress__bar"><div class="lektrail-progress__fill" style="width:%s%%"></div></div></div>',
                $wrapperAttributes,
                esc_html($label),
                esc_attr($percentage)
            );
        }

        return sprintf(
            '<div class="lektrail-progress"><div class="lektrail-progress__label">%s</div>'
            . '<div class="lektrail-progress__bar"><div class="lektrail-progress__fill" style="width:%s%%"></div></div></div>',
            esc_html($label),
            esc_attr($percentage)
        );
    }
}
