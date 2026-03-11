<?php

namespace Completionist\Tests;

use Completionist\Settings;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase {

    public function testDefaultPostTypes(): void {
        $settings = new Settings();

        $this->assertEquals(Settings::DEFAULT_POST_TYPES, $settings->postTypes());
    }

    public function testDefaultMaxViewed(): void {
        $settings = new Settings();

        $this->assertEquals(Settings::DEFAULT_MAX_VIEWED, $settings->maxViewed());
    }

    public function testDefaultMaxRead(): void {
        $settings = new Settings();

        $this->assertEquals(Settings::DEFAULT_MAX_READ, $settings->maxRead());
    }

    public function testDefaultMaxSuggestions(): void {
        $settings = new Settings();

        $this->assertEquals(Settings::DEFAULT_MAX_SUGGESTIONS, $settings->maxSuggestions());
    }

    public function testDefaultPrivacyNotice(): void {
        $settings = new Settings();

        $this->assertEquals(Settings::DEFAULT_PRIVACY_NOTICE, $settings->privacyNotice());
    }

    public function testFromArrayWithCustomValues(): void {
        $settings = Settings::fromArray([
            'post_types' => ['post', 'page', 'book'],
            'max_viewed' => 10,
            'max_read' => 15,
            'max_suggestions' => 8,
            'privacy_notice' => 'Custom notice',
        ]);

        $this->assertEquals(['post', 'page', 'book'], $settings->postTypes());
        $this->assertEquals(10, $settings->maxViewed());
        $this->assertEquals(15, $settings->maxRead());
        $this->assertEquals(8, $settings->maxSuggestions());
        $this->assertEquals('Custom notice', $settings->privacyNotice());
    }

    public function testFromArrayUsesDefaultsForMissingKeys(): void {
        $settings = Settings::fromArray(['max_viewed' => 7]);

        $this->assertEquals(Settings::DEFAULT_POST_TYPES, $settings->postTypes());
        $this->assertEquals(7, $settings->maxViewed());
        $this->assertEquals(Settings::DEFAULT_MAX_READ, $settings->maxRead());
    }

    public function testToArrayReturnsAllValues(): void {
        $settings = new Settings();
        $array = $settings->toArray();

        $this->assertArrayHasKey('post_types', $array);
        $this->assertArrayHasKey('max_viewed', $array);
        $this->assertArrayHasKey('max_read', $array);
        $this->assertArrayHasKey('max_suggestions', $array);
        $this->assertArrayHasKey('privacy_notice', $array);
        $this->assertArrayHasKey('label_continue', $array);
        $this->assertArrayHasKey('label_completed', $array);
        $this->assertArrayHasKey('label_suggestions', $array);
        $this->assertArrayHasKey('label_empty', $array);
        $this->assertArrayHasKey('label_loading', $array);
    }

    public function testLabelsFromArray(): void {
        $settings = Settings::fromArray([
            'label_continue' => 'Keep reading',
            'label_completed' => 'Already read',
            'label_suggestions' => 'You might like',
            'label_empty' => 'Nothing here',
            'label_loading' => 'Please wait...',
        ]);

        $this->assertEquals('Keep reading', $settings->labelContinue());
        $this->assertEquals('Already read', $settings->labelCompleted());
        $this->assertEquals('You might like', $settings->labelSuggestions());
        $this->assertEquals('Nothing here', $settings->labelEmpty());
        $this->assertEquals('Please wait...', $settings->labelLoading());
    }

    public function testToJsConfigReturnsExpectedStructure(): void {
        $settings = Settings::fromArray([
            'max_viewed' => 4,
            'max_read' => 6,
            'max_suggestions' => 8,
            'label_continue' => 'Continue',
            'label_completed' => 'Done',
            'label_suggestions' => 'Try these',
            'label_empty' => 'Empty',
            'label_loading' => 'Loading...',
        ]);

        $config = $settings->toJsConfig();

        $this->assertEquals(4, $config['maxViewed']);
        $this->assertEquals(6, $config['maxRead']);
        $this->assertEquals(8, $config['maxSuggestions']);
        $this->assertEquals('Continue', $config['labels']['continue']);
        $this->assertEquals('Done', $config['labels']['completed']);
        $this->assertEquals('Try these', $config['labels']['suggestions']);
        $this->assertEquals('Empty', $config['labels']['empty']);
        $this->assertEquals('Loading...', $config['labels']['loading']);
    }

    public function testSectionsEnabledByDefault(): void {
        $settings = new Settings();

        $this->assertTrue($settings->viewedEnabled());
        $this->assertTrue($settings->completedEnabled());
    }

    public function testSectionsCanBeDisabled(): void {
        $settings = Settings::fromArray([
            'viewed_enabled' => false,
            'completed_enabled' => false,
        ]);

        $this->assertFalse($settings->viewedEnabled());
        $this->assertFalse($settings->completedEnabled());
    }

    public function testSectionsFromFormStringValues(): void {
        $settings = Settings::fromArray([
            'viewed_enabled' => '0',
            'completed_enabled' => '1',
        ]);

        $this->assertFalse($settings->viewedEnabled());
        $this->assertTrue($settings->completedEnabled());
    }

    public function testToJsConfigIncludesSectionEnabled(): void {
        $settings = Settings::fromArray([
            'viewed_enabled' => false,
            'completed_enabled' => true,
        ]);

        $config = $settings->toJsConfig();

        $this->assertFalse($config['viewedEnabled']);
        $this->assertTrue($config['completedEnabled']);
    }

    public function testDefaultRequireConsentIsFalse(): void {
        $settings = new Settings();

        $this->assertFalse($settings->requireConsent());
    }

    public function testDefaultConsentMessage(): void {
        $settings = new Settings();

        $this->assertEquals(Settings::DEFAULT_CONSENT_MESSAGE, $settings->consentMessage());
    }

    public function testDefaultConsentCheckboxLabel(): void {
        $settings = new Settings();

        $this->assertEquals(Settings::DEFAULT_CONSENT_CHECKBOX_LABEL, $settings->consentCheckboxLabel());
    }

    public function testConsentSettingsFromArray(): void {
        $settings = Settings::fromArray([
            'require_consent' => true,
            'consent_message' => 'Custom consent message',
            'consent_checkbox_label' => 'I agree',
        ]);

        $this->assertTrue($settings->requireConsent());
        $this->assertEquals('Custom consent message', $settings->consentMessage());
        $this->assertEquals('I agree', $settings->consentCheckboxLabel());
    }

    public function testConsentSettingsInToArray(): void {
        $settings = Settings::fromArray([
            'require_consent' => true,
            'consent_message' => 'Test message',
            'consent_checkbox_label' => 'Test label',
        ]);

        $array = $settings->toArray();

        $this->assertArrayHasKey('require_consent', $array);
        $this->assertArrayHasKey('consent_message', $array);
        $this->assertArrayHasKey('consent_checkbox_label', $array);
        $this->assertTrue($array['require_consent']);
        $this->assertEquals('Test message', $array['consent_message']);
        $this->assertEquals('Test label', $array['consent_checkbox_label']);
    }

    public function testToJsConfigIncludesConsentSettings(): void {
        $settings = Settings::fromArray([
            'require_consent' => true,
            'consent_message' => 'Track reading?',
            'consent_checkbox_label' => 'Yes please',
        ]);

        $config = $settings->toJsConfig();

        $this->assertTrue($config['requireConsent']);
        $this->assertEquals('Track reading?', $config['labels']['consentMessage']);
        $this->assertEquals('Yes please', $config['labels']['consentCheckboxLabel']);
    }

    public function testRequireConsentFromFormStringValue(): void {
        $settings = Settings::fromArray([
            'require_consent' => '1',
        ]);

        $this->assertTrue($settings->requireConsent());
    }

    public function testDefaultSuggestionsCacheHours(): void {
        $settings = new Settings();

        $this->assertEquals(24, $settings->suggestionsCacheHours());
    }

    public function testSuggestionsCacheHoursFromArray(): void {
        $settings = Settings::fromArray([
            'suggestions_cache_hours' => 12,
        ]);

        $this->assertEquals(12, $settings->suggestionsCacheHours());
    }

    public function testSuggestionsCacheHoursInToArray(): void {
        $settings = Settings::fromArray([
            'suggestions_cache_hours' => 6,
        ]);

        $array = $settings->toArray();

        $this->assertArrayHasKey('suggestions_cache_hours', $array);
        $this->assertEquals(6, $array['suggestions_cache_hours']);
    }

    public function testToJsConfigIncludesSuggestionsCacheHours(): void {
        $settings = Settings::fromArray([
            'suggestions_cache_hours' => 48,
        ]);

        $config = $settings->toJsConfig();

        $this->assertEquals(48, $config['suggestionsCacheHours']);
    }

    public function testDefaultShowExcerptIsFalse(): void {
        $settings = new Settings();

        $this->assertFalse($settings->showExcerpt());
    }

    public function testDefaultShowThumbnailIsFalse(): void {
        $settings = new Settings();

        $this->assertFalse($settings->showThumbnail());
    }

    public function testShowExcerptFromArray(): void {
        $settings = Settings::fromArray([
            'show_excerpt' => true,
        ]);

        $this->assertTrue($settings->showExcerpt());
    }

    public function testShowThumbnailFromArray(): void {
        $settings = Settings::fromArray([
            'show_thumbnail' => true,
        ]);

        $this->assertTrue($settings->showThumbnail());
    }

    public function testDisplaySettingsInToArray(): void {
        $settings = Settings::fromArray([
            'show_excerpt' => true,
            'show_thumbnail' => true,
        ]);

        $array = $settings->toArray();

        $this->assertArrayHasKey('show_excerpt', $array);
        $this->assertArrayHasKey('show_thumbnail', $array);
        $this->assertTrue($array['show_excerpt']);
        $this->assertTrue($array['show_thumbnail']);
    }

    public function testToJsConfigIncludesDisplaySettings(): void {
        $settings = Settings::fromArray([
            'show_excerpt' => true,
            'show_thumbnail' => false,
        ]);

        $config = $settings->toJsConfig();

        $this->assertTrue($config['showExcerpt']);
        $this->assertFalse($config['showThumbnail']);
    }

    public function testDefaultExcerptLength(): void {
        $settings = new Settings();

        $this->assertEquals(20, $settings->excerptLength());
    }

    public function testExcerptLengthFromArray(): void {
        $settings = Settings::fromArray([
            'excerpt_length' => 30,
        ]);

        $this->assertEquals(30, $settings->excerptLength());
    }

    public function testExcerptLengthInToArray(): void {
        $settings = Settings::fromArray([
            'excerpt_length' => 15,
        ]);

        $array = $settings->toArray();

        $this->assertArrayHasKey('excerpt_length', $array);
        $this->assertEquals(15, $array['excerpt_length']);
    }
}