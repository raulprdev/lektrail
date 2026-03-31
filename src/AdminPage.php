<?php

namespace LekTrail;

use LekTrail\Contracts\Hooks;
use LekTrail\Contracts\PluginConfigRepository;
use LekTrail\WordPress\PluginConfigRepository as WordPressSettingsRepository;

class AdminPage
{
    public const MENU_SLUG = 'lektrail';

    private PluginConfigRepository $pluginConfigs;

    public function __construct(PluginConfigRepository $pluginConfigs)
    {
        $this->pluginConfigs = $pluginConfigs;
    }

    public function register(Hooks $hooks): void
    {
        $hooks->addAction('admin_menu', [$this, 'addMenu']);
        $hooks->addAction('admin_init', [$this, 'registerSettings']);
    }

    public function addMenu(): void
    {
        add_options_page(
            'LekTrail Reading Tracker',
            'LekTrail Reading Tracker',
            'manage_options',
            self::MENU_SLUG,
            [$this, 'render']
        );
    }

    public function registerSettings(): void
    {
        register_setting(
            self::MENU_SLUG,
            WordPressSettingsRepository::OPTION_KEY,
            ['sanitize_callback' => [$this, 'sanitizeSettings']]
        );
    }

    public function sanitizeSettings(array $input): array
    {
        $sanitized = [];

        $sanitized['post_types'] = isset($input['post_types']) && is_array($input['post_types'])
            ? array_map('sanitize_text_field', $input['post_types'])
            : [];

        $sanitized['max_viewed'] = isset($input['max_viewed']) ? absint($input['max_viewed']) : 3;
        $sanitized['max_read'] = isset($input['max_read']) ? absint($input['max_read']) : 5;
        $sanitized['max_suggestions'] = isset($input['max_suggestions']) ? absint($input['max_suggestions']) : 5;

        $sanitized['label_continue'] = isset($input['label_continue'])
            ? sanitize_text_field($input['label_continue']) : '';
        $sanitized['label_completed'] = isset($input['label_completed'])
            ? sanitize_text_field($input['label_completed']) : '';
        $sanitized['label_suggestions'] = isset($input['label_suggestions'])
            ? sanitize_text_field($input['label_suggestions']) : '';
        $sanitized['label_empty'] = isset($input['label_empty'])
            ? sanitize_text_field($input['label_empty']) : '';
        $sanitized['label_loading'] = isset($input['label_loading'])
            ? sanitize_text_field($input['label_loading']) : '';
        $sanitized['label_clear'] = isset($input['label_clear'])
            ? sanitize_text_field($input['label_clear']) : '';

        $sanitized['viewed_enabled'] = !empty($input['viewed_enabled']);
        $sanitized['completed_enabled'] = !empty($input['completed_enabled']);
        $sanitized['require_consent'] = !empty($input['require_consent']);
        $sanitized['show_excerpt'] = !empty($input['show_excerpt']);
        $sanitized['show_thumbnail'] = !empty($input['show_thumbnail']);
        $sanitized['show_clear_button'] = !empty($input['show_clear_button']);
        $sanitized['track_logged_in_users'] = !empty($input['track_logged_in_users']);

        $sanitized['consent_message'] = isset($input['consent_message'])
            ? sanitize_textarea_field($input['consent_message']) : '';
        $sanitized['consent_checkbox_label'] = isset($input['consent_checkbox_label'])
            ? sanitize_text_field($input['consent_checkbox_label']) : '';

        $sanitized['suggestions_cache_hours'] = isset($input['suggestions_cache_hours'])
            ? absint($input['suggestions_cache_hours']) : 24;
        $sanitized['excerpt_length'] = isset($input['excerpt_length'])
            ? absint($input['excerpt_length']) : 20;
        $sanitized['read_threshold'] = isset($input['read_threshold'])
            ? absint($input['read_threshold']) : 90;

        $sanitized['suggestion_order'] = isset($input['suggestion_order'])
            && in_array($input['suggestion_order'], SuggestionsQuery::validOrders(), true)
            ? $input['suggestion_order'] : SuggestionsQuery::ORDER_RANDOM;

        $sanitized['include_categories'] = isset($input['include_categories'])
            && is_array($input['include_categories'])
            ? array_map('absint', $input['include_categories']) : [];
        $sanitized['exclude_categories'] = isset($input['exclude_categories'])
            && is_array($input['exclude_categories'])
            ? array_map('absint', $input['exclude_categories']) : [];

        return $sanitized;
    }

    public function getPluginConfig(): PluginConfig
    {
        return $this->pluginConfigs->load();
    }

    public function render(): void
    {
        $pluginConfig = $this->getPluginConfig();
        include dirname(__DIR__) . '/templates/admin-page.php';
    }
}
