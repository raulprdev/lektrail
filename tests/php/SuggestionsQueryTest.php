<?php

namespace Completionist\Tests;

use Completionist\SuggestionsQuery;
use Completionist\Tests\Mocks\MockPostQuery;
use Completionist\Tests\Mocks\MockSettingsRepository;
use PHPUnit\Framework\TestCase;

class SuggestionsQueryTest extends TestCase
{
    private MockPostQuery $postQuery;

    protected function setUp(): void
    {
        $this->postQuery        = new MockPostQuery();
        $this->postQuery->posts = [
            ['id' => 1, 'title' => 'Post 1', 'url' => '/post-1'],
            ['id' => 2, 'title' => 'Post 2', 'url' => '/post-2'],
            ['id' => 3, 'title' => 'Post 3', 'url' => '/post-3'],
        ];
    }

    public function testExcludesSpecifiedIds(): void
    {
        $settings = new MockSettingsRepository();
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $query->get([12, 45, 78]);

        $this->assertEquals([12, 45, 78], $this->postQuery->lastQueryArgs['post__not_in']);
    }

    public function testUsesDateOrderAndFetchesExtraForShuffle(): void
    {
        $settings = new MockSettingsRepository(['max_suggestions' => 5]);
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $query->get([]);

        $this->assertEquals('date', $this->postQuery->lastQueryArgs['orderby']);
        $this->assertEquals('DESC', $this->postQuery->lastQueryArgs['order']);
        $this->assertGreaterThan(5, $this->postQuery->lastQueryArgs['posts_per_page']);
    }

    public function testReturnsOnlyMaxSuggestionsAfterShuffle(): void
    {
        $this->postQuery->posts = [];
        for ($i = 1; $i <= 20; $i++) {
            $this->postQuery->posts[] = [ 'id' => $i, 'title' => "Post $i", 'url' => "/post-$i"];
        }
        $settings = new MockSettingsRepository(['max_suggestions' => 5]);
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $result = $query->get([]);

        $this->assertCount(5, $result);
    }

    public function testUsesRecentOrderWhenConfigured(): void
    {
        $settings = new MockSettingsRepository(['suggestion_order' => 'recent']);
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $query->get([]);

        $this->assertEquals('date', $this->postQuery->lastQueryArgs['orderby']);
        $this->assertEquals('DESC', $this->postQuery->lastQueryArgs['order']);
    }

    public function testUsesRelatedOrderWithCategories(): void
    {
        $settings = new MockSettingsRepository(['suggestion_order' => 'related']);
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $query->get([10, 20], [5, 7]);

        $this->assertEquals([5, 7], $this->postQuery->lastQueryArgs['category__in']);
        $this->assertEquals('date', $this->postQuery->lastQueryArgs['orderby']);
    }

    public function testRelatedFallsBackToShuffleWhenNoCategories(): void
    {
        $settings = new MockSettingsRepository(['suggestion_order' => 'related']);
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $query->get([]);

        $this->assertEquals('date', $this->postQuery->lastQueryArgs['orderby']);
        $this->assertArrayNotHasKey('category__in', $this->postQuery->lastQueryArgs);
    }

    public function testFiltersIncludeCategories(): void
    {
        $settings = new MockSettingsRepository(['include_categories' => [1, 2, 3]]);
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $query->get([]);

        $this->assertEquals([1, 2, 3], $this->postQuery->lastQueryArgs['category__in']);
    }

    public function testFiltersExcludeCategories(): void
    {
        $settings = new MockSettingsRepository(['exclude_categories' => [4, 5]]);
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $query->get([]);

        $this->assertEquals([4, 5], $this->postQuery->lastQueryArgs['category__not_in']);
    }

    public function testRelatedIgnoresIncludeExcludeCategories(): void
    {
        $settings = new MockSettingsRepository([
            'suggestion_order' => 'related',
            'include_categories' => [1, 2],
            'exclude_categories' => [3, 4],
        ]);
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $query->get([], [5, 6]);

        $this->assertEquals([5, 6], $this->postQuery->lastQueryArgs['category__in']);
        $this->assertArrayNotHasKey('category__not_in', $this->postQuery->lastQueryArgs);
    }

    public function testUsesConfiguredPostTypes(): void
    {
        $settings = new MockSettingsRepository(['post_types' => ['post', 'page', 'book']]);
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $query->get([]);

        $this->assertEquals(['post', 'page', 'book'], $this->postQuery->lastQueryArgs['post_type']);
    }

    public function testFetchesMultipleOfMaxSuggestionsForShuffle(): void
    {
        $settings = new MockSettingsRepository(['max_suggestions' => 10]);
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $query->get([]);

        $this->assertEquals(30, $this->postQuery->lastQueryArgs['posts_per_page']);
    }

    public function testAlwaysFiltersPublishedPosts(): void
    {
        $settings = new MockSettingsRepository();
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $query->get([]);

        $this->assertEquals('publish', $this->postQuery->lastQueryArgs['post_status']);
    }

    public function testReturnsPostsFromQuery(): void
    {
        $this->postQuery->posts = [
            ['id' => 10, 'title' => 'Alpha'],
            ['id' => 20, 'title' => 'Beta'],
        ];
        $settings               = new MockSettingsRepository();
        $query                  = new SuggestionsQuery($settings, $this->postQuery);

        $result = $query->get([]);

        $this->assertCount(2, $result);
        $titles = array_column($result, 'title');
        $this->assertContains('Alpha', $titles);
        $this->assertContains('Beta', $titles);
    }

    public function testExcludesPostsById(): void
    {
        $settings = new MockSettingsRepository();
        $query = new SuggestionsQuery($settings, $this->postQuery);

        $result = $query->get([1, 2]);

        $this->assertCount(1, $result);
        $this->assertEquals('Post 3', $result[0]['title']);
    }
}
