<?php

namespace LekTrail\Renderers;

use LekTrail\Contracts\PostQuery;
use LekTrail\Contracts\ReadingHistoryRepository;
use LekTrail\Contracts\ScriptLoader;
use LekTrail\Contracts\UserProvider;
use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Dashboard\ReadingHistory;

class ReadingListRenderer
{
    private ReadingHistoryRepository $history;
    private PostQuery $postQuery;
    private UserProvider $users;
    private ScriptLoader $scripts;

    public function __construct(
        ReadingHistoryRepository $history,
        PostQuery $postQuery,
        UserProvider $users,
        ScriptLoader $scripts
    ) {
        $this->history = $history;
        $this->postQuery = $postQuery;
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
        $filtered = $history->filter($filter);
        $postIds = $filtered->postIds();

        $this->enqueueStyle();

        $items = empty($postIds) ? [] : $this->postQuery->getPostsDataByIds($postIds);
        $listHtml = $this->renderItems($items);

        if ($wrapperAttributes) {
            return sprintf('<div %s>%s</div>', $wrapperAttributes, $listHtml);
        }

        return sprintf('<div class="lektrail-reading-list">%s</div>', $listHtml);
    }

    private function renderItems(array $items): string
    {
        if (empty($items)) {
            return '';
        }

        $html = '<ul>';
        foreach ($items as $item) {
            $html .= sprintf(
                '<li><a href="%s">%s</a></li>',
                esc_url($item['url']),
                esc_html($item['title'])
            );
        }
        $html .= '</ul>';

        return $html;
    }

    private function enqueueStyle(): void
    {
        $this->scripts->enqueueStyle('lektrail-dashboard', '', [], '');
    }
}
