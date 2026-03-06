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
}