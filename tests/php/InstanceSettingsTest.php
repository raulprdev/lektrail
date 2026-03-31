<?php

namespace LekTrail\Tests;

use LekTrail\InstanceSettings;
use LekTrail\PluginConfig;
use PHPUnit\Framework\TestCase;

class InstanceSettingsTest extends TestCase
{
    public function testMergeOverridesGlobalSettings(): void
    {
        $global = PluginConfig::fromArray([ 'max_viewed' => 3, 'max_read' => 5]);
        $overrides = ['max_viewed' => 10];

        $merged = InstanceSettings::merge($global, $overrides);

        $this->assertEquals(10, $merged->maxViewed());
        $this->assertEquals(5, $merged->maxRead());
    }

    public function testMergePreservesUnspecifiedValues(): void
    {
        $global = PluginConfig::fromArray([
            'max_viewed' => 3,
            'show_excerpt' => true,
            'label_continue' => 'Keep reading',
        ]);
        $overrides = ['max_viewed' => 7];

        $merged = InstanceSettings::merge($global, $overrides);

        $this->assertEquals(7, $merged->maxViewed());
        $this->assertTrue($merged->showExcerpt());
        $this->assertEquals('Keep reading', $merged->labelContinue());
    }

    public function testMergeIgnoresNonInstanceKeys(): void
    {
        $global = PluginConfig::fromArray([ 'post_types' => ['post'], 'max_viewed' => 3]);
        $overrides = ['post_types' => ['page'], 'max_viewed' => 5];

        $merged = InstanceSettings::merge($global, $overrides);

        $this->assertEquals(['post'], $merged->postTypes());
        $this->assertEquals(5, $merged->maxViewed());
    }

    public function testMergeReturnsGlobalWhenNoOverrides(): void
    {
        $global = PluginConfig::fromArray([ 'max_viewed' => 3]);
        $overrides = [];

        $merged = InstanceSettings::merge($global, $overrides);

        $this->assertSame($global, $merged);
    }

    public function testFromBlockAttributesConvertsCamelToSnake(): void
    {
        $attributes = [
            'maxViewed' => 5,
            'showExcerpt' => true,
            'labelContinue' => 'Read more',
        ];

        $result = InstanceSettings::fromBlockAttributes($attributes);

        $this->assertEquals([
            'max_viewed' => 5,
            'show_excerpt' => true,
            'label_continue' => 'Read more',
        ], $result);
    }

    public function testFromBlockAttributesIgnoresNullValues(): void
    {
        $attributes = [
            'maxViewed' => 5,
            'maxRead' => null,
            'showExcerpt' => true,
        ];

        $result = InstanceSettings::fromBlockAttributes($attributes);

        $this->assertArrayHasKey('max_viewed', $result);
        $this->assertArrayNotHasKey('max_read', $result);
        $this->assertArrayHasKey('show_excerpt', $result);
    }

    public function testFromBlockAttributesIgnoresUnknownAttributes(): void
    {
        $attributes = [
            'maxViewed' => 5,
            'unknownAttribute' => 'value',
            'className' => 'custom-class',
        ];

        $result = InstanceSettings::fromBlockAttributes($attributes);

        $this->assertEquals(['max_viewed' => 5], $result);
    }

    public function testFromShortcodeAttributesCastsIntegers(): void
    {
        $atts = [
            'max_viewed' => '5',
            'max_read' => '10',
            'excerpt_length' => '25',
        ];

        $result = InstanceSettings::fromShortcodeAttributes($atts);

        $this->assertSame(5, $result['max_viewed']);
        $this->assertSame(10, $result['max_read']);
        $this->assertSame(25, $result['excerpt_length']);
    }

    public function testFromShortcodeAttributesCastsBooleans(): void
    {
        $atts = [
            'show_excerpt' => 'true',
            'show_thumbnail' => 'false',
            'viewed_enabled' => '1',
            'completed_enabled' => '0',
        ];

        $result = InstanceSettings::fromShortcodeAttributes($atts);

        $this->assertTrue($result['show_excerpt']);
        $this->assertFalse($result['show_thumbnail']);
        $this->assertTrue($result['viewed_enabled']);
        $this->assertFalse($result['completed_enabled']);
    }

    public function testFromShortcodeAttributesIgnoresEmptyStrings(): void
    {
        $atts = [
            'max_viewed' => '5',
            'max_read' => '',
            'label_continue' => '',
        ];

        $result = InstanceSettings::fromShortcodeAttributes($atts);

        $this->assertArrayHasKey('max_viewed', $result);
        $this->assertArrayNotHasKey('max_read', $result);
        $this->assertArrayNotHasKey('label_continue', $result);
    }

    public function testFromShortcodeAttributesPreservesStrings(): void
    {
        $atts = [
            'label_continue' => 'Keep reading',
            'suggestion_order' => 'recent',
        ];

        $result = InstanceSettings::fromShortcodeAttributes($atts);

        $this->assertEquals('Keep reading', $result['label_continue']);
        $this->assertEquals('recent', $result['suggestion_order']);
    }
}
