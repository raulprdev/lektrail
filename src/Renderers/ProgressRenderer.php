<?php

namespace LekTrail\Renderers;

use LekTrail\Contracts\PostCountRepository;
use LekTrail\Contracts\ReadingHistoryRepository;
use LekTrail\Contracts\ScriptLoader;
use LekTrail\Contracts\UserProvider;
use LekTrail\Dashboard\ProgressCalculator;
use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Dashboard\ReadingHistory;

class ProgressRenderer
{
    private ReadingHistoryRepository $history;
    private PostCountRepository $counts;
    private UserProvider $users;
    private ScriptLoader $scripts;

    public function __construct(
        ReadingHistoryRepository $history,
        PostCountRepository $counts,
        UserProvider $users,
        ScriptLoader $scripts
    ) {
        $this->history = $history;
        $this->counts = $counts;
        $this->users = $users;
        $this->scripts = $scripts;
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

        $this->enqueueStyle();

        $percentage = $stats->percentage();
        $label = sprintf('%d of %d (%s%%)', $stats->read(), $stats->total(), $percentage);

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

    private function enqueueStyle(): void
    {
        $this->scripts->enqueueStyle('lektrail-dashboard', '', [], '');
    }
}
