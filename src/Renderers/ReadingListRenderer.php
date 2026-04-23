<?php

namespace LekTrail\Renderers;

use LekTrail\Assets;
use LekTrail\Contracts\CategoryResolver;
use LekTrail\Contracts\PostQuery;
use LekTrail\Contracts\ReadingHistoryRepository;
use LekTrail\Contracts\UserProvider;
use LekTrail\Dashboard\ReadingFilter;
use LekTrail\Dashboard\ReadingHistory;

class ReadingListRenderer
{
    private ReadingHistoryRepository $history;
    private PostQuery $postQuery;
    private UserProvider $users;
    private Assets $assets;
    private CategoryResolver $categoryResolver;

    public function __construct(
        ReadingHistoryRepository $history,
        PostQuery $postQuery,
        UserProvider $users,
        Assets $assets,
        CategoryResolver $categoryResolver
    ) {
        $this->history = $history;
        $this->postQuery = $postQuery;
        $this->users = $users;
        $this->assets = $assets;
        $this->categoryResolver = $categoryResolver;
    }

    public function render(ReadingFilter $filter, string $wrapperAttributes = ''): string
    {
        if (!$this->users->isLoggedIn()) {
            return '';
        }

        $filter = $this->resolveCategories($filter);
        $records = $this->history->getAllForUser($this->users->getCurrentUserId());
        $history = new ReadingHistory($records);
        $filtered = $history->filter($filter);
        $postIds = $filtered->postIds();

        $this->assets->enqueueDashboardStyle();

        $items = empty($postIds) ? [] : $this->postQuery->getPostsDataByIds($postIds);
        $statusMap = $filtered->statusMap();
        $listHtml = $this->renderItems($items, $statusMap);

        if ($wrapperAttributes) {
            return sprintf('<div %s>%s</div>', $wrapperAttributes, $listHtml);
        }

        return sprintf('<div class="lektrail-reading-list">%s</div>', $listHtml);
    }

    private function renderItems(array $items, array $statusMap): string
    {
        if (empty($items)) {
            return '';
        }

        $html = '<ul>';
        foreach ($items as $item) {
            $status = $statusMap[$item['id']] ?? 'viewed';
            $html .= sprintf(
                '<li class="lektrail-reading-list__item lektrail-reading-list__item--%s"><a href="%s">%s</a></li>',
                esc_attr($status),
                esc_url($item['url']),
                esc_html($item['title'])
            );
        }
        $html .= '</ul>';

        return $html;
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
