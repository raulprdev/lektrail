<?php

namespace Completionist\Tests;

use Completionist\PluginConfig;
use PHPUnit\Framework\TestCase;

class PluginConfigTest extends TestCase
{
    public function testDefaultPostTypes(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals(PluginConfig::DEFAULT_POST_TYPES, $pluginConfig->postTypes());
    }

    public function testDefaultMaxViewed(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals(PluginConfig::DEFAULT_MAX_VIEWED, $pluginConfig->maxViewed());
    }

    public function testDefaultMaxRead(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals(PluginConfig::DEFAULT_MAX_READ, $pluginConfig->maxRead());
    }

    public function testDefaultMaxSuggestions(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals(PluginConfig::DEFAULT_MAX_SUGGESTIONS, $pluginConfig->maxSuggestions());
    }

    public function testFromArrayWithCustomValues(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'post_types' => ['post', 'page', 'book'],
            'max_viewed' => 10,
            'max_read' => 15,
            'max_suggestions' => 8,
        ]);

        $this->assertEquals(['post', 'page', 'book'], $pluginConfig->postTypes());
        $this->assertEquals(10, $pluginConfig->maxViewed());
        $this->assertEquals(15, $pluginConfig->maxRead());
        $this->assertEquals(8, $pluginConfig->maxSuggestions());
    }

    public function testFromArrayUsesDefaultsForMissingKeys(): void
    {
        $pluginConfig = PluginConfig::fromArray([ 'max_viewed' => 7]);

        $this->assertEquals(PluginConfig::DEFAULT_POST_TYPES, $pluginConfig->postTypes());
        $this->assertEquals(7, $pluginConfig->maxViewed());
        $this->assertEquals(PluginConfig::DEFAULT_MAX_READ, $pluginConfig->maxRead());
    }

    public function testToArrayReturnsAllValues(): void
    {
        $pluginConfig = new PluginConfig();
        $array = $pluginConfig->toArray();

        $this->assertArrayHasKey('post_types', $array);
        $this->assertArrayHasKey('max_viewed', $array);
        $this->assertArrayHasKey('max_read', $array);
        $this->assertArrayHasKey('max_suggestions', $array);
        $this->assertArrayHasKey('label_continue', $array);
        $this->assertArrayHasKey('label_completed', $array);
        $this->assertArrayHasKey('label_suggestions', $array);
        $this->assertArrayHasKey('label_empty', $array);
        $this->assertArrayHasKey('label_loading', $array);
    }

    public function testLabelsFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'label_continue' => 'Keep reading',
            'label_completed' => 'Already read',
            'label_suggestions' => 'You might like',
            'label_empty' => 'Nothing here',
            'label_loading' => 'Please wait...',
        ]);

        $this->assertEquals('Keep reading', $pluginConfig->labelContinue());
        $this->assertEquals('Already read', $pluginConfig->labelCompleted());
        $this->assertEquals('You might like', $pluginConfig->labelSuggestions());
        $this->assertEquals('Nothing here', $pluginConfig->labelEmpty());
        $this->assertEquals('Please wait...', $pluginConfig->labelLoading());
    }

    public function testToJsConfigReturnsExpectedStructure(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'max_viewed' => 4,
            'max_read' => 6,
            'max_suggestions' => 8,
            'label_continue' => 'Continue',
            'label_completed' => 'Done',
            'label_suggestions' => 'Try these',
            'label_empty' => 'Empty',
            'label_loading' => 'Loading...',
        ]);

        $config = $pluginConfig->toJsConfig();

        $this->assertEquals(4, $config['maxViewed']);
        $this->assertEquals(6, $config['maxRead']);
        $this->assertEquals(8, $config['maxSuggestions']);
        $this->assertEquals('Continue', $config['labels']['continue']);
        $this->assertEquals('Done', $config['labels']['completed']);
        $this->assertEquals('Try these', $config['labels']['suggestions']);
        $this->assertEquals('Empty', $config['labels']['empty']);
        $this->assertEquals('Loading...', $config['labels']['loading']);
    }

    public function testSectionsEnabledByDefault(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertTrue($pluginConfig->viewedEnabled());
        $this->assertTrue($pluginConfig->completedEnabled());
    }

    public function testSectionsCanBeDisabled(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'viewed_enabled' => false,
            'completed_enabled' => false,
        ]);

        $this->assertFalse($pluginConfig->viewedEnabled());
        $this->assertFalse($pluginConfig->completedEnabled());
    }

    public function testSectionsFromFormStringValues(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'viewed_enabled' => '0',
            'completed_enabled' => '1',
        ]);

        $this->assertFalse($pluginConfig->viewedEnabled());
        $this->assertTrue($pluginConfig->completedEnabled());
    }

    public function testToJsConfigIncludesSectionEnabled(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'viewed_enabled' => false,
            'completed_enabled' => true,
        ]);

        $config = $pluginConfig->toJsConfig();

        $this->assertFalse($config['viewedEnabled']);
        $this->assertTrue($config['completedEnabled']);
    }

    public function testDefaultRequireConsentIsFalse(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertFalse($pluginConfig->requireConsent());
    }

    public function testDefaultConsentMessage(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals(PluginConfig::DEFAULT_CONSENT_MESSAGE, $pluginConfig->consentMessage());
    }

    public function testDefaultConsentCheckboxLabel(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals(PluginConfig::DEFAULT_CONSENT_CHECKBOX_LABEL, $pluginConfig->consentCheckboxLabel());
    }

    public function testConsentSettingsFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'require_consent' => true,
            'consent_message' => 'Custom consent message',
            'consent_checkbox_label' => 'I agree',
        ]);

        $this->assertTrue($pluginConfig->requireConsent());
        $this->assertEquals('Custom consent message', $pluginConfig->consentMessage());
        $this->assertEquals('I agree', $pluginConfig->consentCheckboxLabel());
    }

    public function testConsentSettingsInToArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'require_consent' => true,
            'consent_message' => 'Test message',
            'consent_checkbox_label' => 'Test label',
        ]);

        $array = $pluginConfig->toArray();

        $this->assertArrayHasKey('require_consent', $array);
        $this->assertArrayHasKey('consent_message', $array);
        $this->assertArrayHasKey('consent_checkbox_label', $array);
        $this->assertTrue($array['require_consent']);
        $this->assertEquals('Test message', $array['consent_message']);
        $this->assertEquals('Test label', $array['consent_checkbox_label']);
    }

    public function testToJsConfigIncludesConsentSettings(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'require_consent' => true,
            'consent_message' => 'Track reading?',
            'consent_checkbox_label' => 'Yes please',
        ]);

        $config = $pluginConfig->toJsConfig();

        $this->assertTrue($config['requireConsent']);
        $this->assertEquals('Track reading?', $config['labels']['consentMessage']);
        $this->assertEquals('Yes please', $config['labels']['consentCheckboxLabel']);
    }

    public function testRequireConsentFromFormStringValue(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'require_consent' => '1',
        ]);

        $this->assertTrue($pluginConfig->requireConsent());
    }

    public function testDefaultSuggestionsCacheHours(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals(24, $pluginConfig->suggestionsCacheHours());
    }

    public function testSuggestionsCacheHoursFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'suggestions_cache_hours' => 12,
        ]);

        $this->assertEquals(12, $pluginConfig->suggestionsCacheHours());
    }

    public function testSuggestionsCacheHoursInToArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'suggestions_cache_hours' => 6,
        ]);

        $array = $pluginConfig->toArray();

        $this->assertArrayHasKey('suggestions_cache_hours', $array);
        $this->assertEquals(6, $array['suggestions_cache_hours']);
    }

    public function testToJsConfigIncludesSuggestionsCacheHours(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'suggestions_cache_hours' => 48,
        ]);

        $config = $pluginConfig->toJsConfig();

        $this->assertEquals(48, $config['suggestionsCacheHours']);
    }

    public function testDefaultShowExcerptIsFalse(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertFalse($pluginConfig->showExcerpt());
    }

    public function testDefaultShowThumbnailIsFalse(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertFalse($pluginConfig->showThumbnail());
    }

    public function testShowExcerptFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'show_excerpt' => true,
        ]);

        $this->assertTrue($pluginConfig->showExcerpt());
    }

    public function testShowThumbnailFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'show_thumbnail' => true,
        ]);

        $this->assertTrue($pluginConfig->showThumbnail());
    }

    public function testDisplaySettingsInToArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'show_excerpt' => true,
            'show_thumbnail' => true,
        ]);

        $array = $pluginConfig->toArray();

        $this->assertArrayHasKey('show_excerpt', $array);
        $this->assertArrayHasKey('show_thumbnail', $array);
        $this->assertTrue($array['show_excerpt']);
        $this->assertTrue($array['show_thumbnail']);
    }

    public function testToJsConfigIncludesDisplaySettings(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'show_excerpt' => true,
            'show_thumbnail' => false,
        ]);

        $config = $pluginConfig->toJsConfig();

        $this->assertTrue($config['showExcerpt']);
        $this->assertFalse($config['showThumbnail']);
    }

    public function testDefaultExcerptLength(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals(20, $pluginConfig->excerptLength());
    }

    public function testExcerptLengthFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'excerpt_length' => 30,
        ]);

        $this->assertEquals(30, $pluginConfig->excerptLength());
    }

    public function testExcerptLengthInToArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'excerpt_length' => 15,
        ]);

        $array = $pluginConfig->toArray();

        $this->assertArrayHasKey('excerpt_length', $array);
        $this->assertEquals(15, $array['excerpt_length']);
    }

    public function testDefaultReadThreshold(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals(90, $pluginConfig->readThreshold());
    }

    public function testReadThresholdFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'read_threshold' => 50,
        ]);

        $this->assertEquals(50, $pluginConfig->readThreshold());
    }

    public function testReadThresholdInToArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'read_threshold' => 75,
        ]);

        $array = $pluginConfig->toArray();

        $this->assertArrayHasKey('read_threshold', $array);
        $this->assertEquals(75, $array['read_threshold']);
    }

    public function testToJsConfigIncludesReadThreshold(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'read_threshold' => 80,
        ]);

        $config = $pluginConfig->toJsConfig();

        $this->assertEquals(80, $config['readThreshold']);
    }

    public function testDefaultSuggestionOrder(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals('random', $pluginConfig->suggestionOrder());
    }

    public function testSuggestionOrderFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'suggestion_order' => 'recent',
        ]);

        $this->assertEquals('recent', $pluginConfig->suggestionOrder());
    }

    public function testDefaultIncludeCategories(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals([], $pluginConfig->includeCategories());
    }

    public function testIncludeCategoriesFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'include_categories' => [1, 3, 5],
        ]);

        $this->assertEquals([1, 3, 5], $pluginConfig->includeCategories());
    }

    public function testDefaultExcludeCategories(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals([], $pluginConfig->excludeCategories());
    }

    public function testExcludeCategoriesFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'exclude_categories' => [2, 4],
        ]);

        $this->assertEquals([2, 4], $pluginConfig->excludeCategories());
    }

    public function testSuggestionSettingsInToArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'suggestion_order' => 'related',
            'include_categories' => [1, 2],
            'exclude_categories' => [3, 4],
        ]);

        $array = $pluginConfig->toArray();

        $this->assertArrayHasKey('suggestion_order', $array);
        $this->assertArrayHasKey('include_categories', $array);
        $this->assertArrayHasKey('exclude_categories', $array);
        $this->assertEquals('related', $array['suggestion_order']);
        $this->assertEquals([1, 2], $array['include_categories']);
        $this->assertEquals([3, 4], $array['exclude_categories']);
    }

    public function testDefaultShowClearButton(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertTrue($pluginConfig->showClearButton());
    }

    public function testShowClearButtonFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([ 'show_clear_button' => false]);

        $this->assertFalse($pluginConfig->showClearButton());
    }

    public function testDefaultLabelClear(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertEquals(PluginConfig::DEFAULT_LABEL_CLEAR, $pluginConfig->labelClear());
    }

    public function testLabelClearFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([ 'label_clear' => 'Borrar mis datos']);

        $this->assertEquals('Borrar mis datos', $pluginConfig->labelClear());
    }

    public function testToJsConfigIncludesClearSettings(): void
    {
        $pluginConfig = PluginConfig::fromArray([
            'show_clear_button' => true,
            'label_clear' => 'Clear data',
        ]);

        $jsConfig = $pluginConfig->toJsConfig();

        $this->assertTrue($jsConfig['showClearButton']);
        $this->assertEquals('Clear data', $jsConfig['labels']['clear']);
    }

    public function testDefaultTrackLoggedInUsersIsFalse(): void
    {
        $pluginConfig = new PluginConfig();

        $this->assertFalse($pluginConfig->trackLoggedInUsers());
    }

    public function testTrackLoggedInUsersFromArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([ 'track_logged_in_users' => true]);

        $this->assertTrue($pluginConfig->trackLoggedInUsers());
    }

    public function testTrackLoggedInUsersInToArray(): void
    {
        $pluginConfig = PluginConfig::fromArray([ 'track_logged_in_users' => true]);

        $array = $pluginConfig->toArray();

        $this->assertArrayHasKey('track_logged_in_users', $array);
        $this->assertTrue($array['track_logged_in_users']);
    }
}
