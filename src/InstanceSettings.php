<?php

namespace Completionist;

class InstanceSettings
{
    private const INSTANCE_KEYS = [
        'max_viewed',
        'max_read',
        'max_suggestions',
        'show_excerpt',
        'show_thumbnail',
        'excerpt_length',
        'viewed_enabled',
        'completed_enabled',
        'show_clear_button',
        'label_continue',
        'label_completed',
        'label_suggestions',
        'label_empty',
        'label_loading',
        'label_clear',
        'suggestion_order',
        'suggestions_cache_hours',
    ];

    private const CAMEL_TO_SNAKE = [
        'maxViewed' => 'max_viewed',
        'maxRead' => 'max_read',
        'maxSuggestions' => 'max_suggestions',
        'showExcerpt' => 'show_excerpt',
        'showThumbnail' => 'show_thumbnail',
        'excerptLength' => 'excerpt_length',
        'viewedEnabled' => 'viewed_enabled',
        'completedEnabled' => 'completed_enabled',
        'showClearButton' => 'show_clear_button',
        'labelContinue' => 'label_continue',
        'labelCompleted' => 'label_completed',
        'labelSuggestions' => 'label_suggestions',
        'labelEmpty' => 'label_empty',
        'labelLoading' => 'label_loading',
        'labelClear' => 'label_clear',
        'suggestionOrder' => 'suggestion_order',
        'suggestionsCacheHours' => 'suggestions_cache_hours',
    ];

    private const BOOLEAN_KEYS = [
        'show_excerpt',
        'show_thumbnail',
        'viewed_enabled',
        'completed_enabled',
        'show_clear_button',
    ];

    private const INTEGER_KEYS = [
        'max_viewed',
        'max_read',
        'max_suggestions',
        'excerpt_length',
        'suggestions_cache_hours',
    ];

    public static function merge(PluginConfig $global, array $overrides): PluginConfig
    {
        $filtered = self::filterInstanceKeys($overrides);
        if (empty($filtered)) {
            return $global;
        }
        return PluginConfig::fromArray(array_merge($global->toArray(), $filtered));
    }

    public static function fromBlockAttributes(array $attributes): array
    {
        $result = [];
        foreach (self::CAMEL_TO_SNAKE as $camel => $snake) {
            if (array_key_exists($camel, $attributes) && $attributes[$camel] !== null) {
                $result[$snake] = $attributes[$camel];
            }
        }
        return $result;
    }

    public static function fromShortcodeAttributes(array $atts): array
    {
        $result = [];
        foreach (self::INSTANCE_KEYS as $key) {
            if (isset($atts[$key]) && $atts[$key] !== '') {
                $result[$key] = self::castValue($key, $atts[$key]);
            }
        }
        return $result;
    }

    private static function filterInstanceKeys(array $data): array
    {
        return array_filter(
            $data,
            fn ($key) => in_array($key, self::INSTANCE_KEYS, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    private static function castValue(string $key, $value)
    {
        if (in_array($key, self::BOOLEAN_KEYS, true)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        if (in_array($key, self::INTEGER_KEYS, true)) {
            return (int) $value;
        }
        return $value;
    }
}
