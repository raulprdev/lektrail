<?php

namespace Completionist\Tests;

use Completionist\SuggestionsQuery;
use Completionist\Tests\Mocks\MockPostQuery;
use Completionist\Tests\Mocks\MockSettingsRepository;
use PHPUnit\Framework\TestCase;

class SuggestionsQueryTest extends TestCase {

    private MockPostQuery $posts;

    protected function setUp(): void {
        $this->posts = new MockPostQuery();
        $this->posts->posts = [
            ['id' => 1, 'title' => 'Post 1', 'url' => '/post-1'],
            ['id' => 2, 'title' => 'Post 2', 'url' => '/post-2'],
            ['id' => 3, 'title' => 'Post 3', 'url' => '/post-3'],
        ];
    }

    public function testExcludesSpecifiedIds(): void {
        $settings = new MockSettingsRepository();
        $query = new SuggestionsQuery($settings, $this->posts);

        $query->get([12, 45, 78]);

        $this->assertEquals([12, 45, 78], $this->posts->lastQueryArgs['post__not_in']);
    }

    public function testUsesRandomOrderByDefault(): void {
        $settings = new MockSettingsRepository();
        $query = new SuggestionsQuery($settings, $this->posts);

        $query->get([]);

        $this->assertEquals('rand', $this->posts->lastQueryArgs['orderby']);
    }

    public function testUsesRecentOrderWhenConfigured(): void {
        $settings = new MockSettingsRepository(['suggestion_order' => 'recent']);
        $query = new SuggestionsQuery($settings, $this->posts);

        $query->get([]);

        $this->assertEquals('date', $this->posts->lastQueryArgs['orderby']);
        $this->assertEquals('DESC', $this->posts->lastQueryArgs['order']);
    }

    public function testUsesRelatedOrderWithCategories(): void {
        $settings = new MockSettingsRepository(['suggestion_order' => 'related']);
        $query = new SuggestionsQuery($settings, $this->posts);

        $query->get([10, 20], [5, 7]);

        $this->assertEquals([5, 7], $this->posts->lastQueryArgs['category__in']);
        $this->assertEquals('rand', $this->posts->lastQueryArgs['orderby']);
    }

    public function testRelatedFallsBackToRandomWhenNoCategories(): void {
        $settings = new MockSettingsRepository(['suggestion_order' => 'related']);
        $query = new SuggestionsQuery($settings, $this->posts);

        $query->get([]);

        $this->assertEquals('rand', $this->posts->lastQueryArgs['orderby']);
        $this->assertArrayNotHasKey('category__in', $this->posts->lastQueryArgs);
    }

    public function testFiltersIncludeCategories(): void {
        $settings = new MockSettingsRepository(['include_categories' => [1, 2, 3]]);
        $query = new SuggestionsQuery($settings, $this->posts);

        $query->get([]);

        $this->assertEquals([1, 2, 3], $this->posts->lastQueryArgs['category__in']);
    }

    public function testFiltersExcludeCategories(): void {
        $settings = new MockSettingsRepository(['exclude_categories' => [4, 5]]);
        $query = new SuggestionsQuery($settings, $this->posts);

        $query->get([]);

        $this->assertEquals([4, 5], $this->posts->lastQueryArgs['category__not_in']);
    }

    public function testRelatedIgnoresIncludeExcludeCategories(): void {
        $settings = new MockSettingsRepository([
            'suggestion_order' => 'related',
            'include_categories' => [1, 2],
            'exclude_categories' => [3, 4],
        ]);
        $query = new SuggestionsQuery($settings, $this->posts);

        $query->get([], [5, 6]);

        $this->assertEquals([5, 6], $this->posts->lastQueryArgs['category__in']);
        $this->assertArrayNotHasKey('category__not_in', $this->posts->lastQueryArgs);
    }

    public function testUsesConfiguredPostTypes(): void {
        $settings = new MockSettingsRepository(['post_types' => ['post', 'page', 'book']]);
        $query = new SuggestionsQuery($settings, $this->posts);

        $query->get([]);

        $this->assertEquals(['post', 'page', 'book'], $this->posts->lastQueryArgs['post_type']);
    }

    public function testUsesConfiguredMaxSuggestions(): void {
        $settings = new MockSettingsRepository(['max_suggestions' => 10]);
        $query = new SuggestionsQuery($settings, $this->posts);

        $query->get([]);

        $this->assertEquals(10, $this->posts->lastQueryArgs['posts_per_page']);
    }

    public function testAlwaysFiltersPublishedPosts(): void {
        $settings = new MockSettingsRepository();
        $query = new SuggestionsQuery($settings, $this->posts);

        $query->get([]);

        $this->assertEquals('publish', $this->posts->lastQueryArgs['post_status']);
    }

    public function testReturnsPostsFromQuery(): void {
        $settings = new MockSettingsRepository();
        $query = new SuggestionsQuery($settings, $this->posts);

        $result = $query->get([]);

        $this->assertCount(3, $result);
        $this->assertEquals('Post 1', $result[0]['title']);
    }

    public function testExcludesPostsById(): void {
        $settings = new MockSettingsRepository();
        $query = new SuggestionsQuery($settings, $this->posts);

        $result = $query->get([1, 2]);

        $this->assertCount(1, $result);
        $this->assertEquals('Post 3', $result[0]['title']);
    }
}