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
    }
}