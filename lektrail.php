<?php
/**
 * Plugin Name:       LekTrail Reading Tracker
 * Description:       Track reading progress and suggest unread posts.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            raulprdev
 * License:           GPL v2 or later
 * Text Domain:       lektrail-reading-tracker
 */

defined('ABSPATH') || exit;

register_activation_hook(__FILE__, function () {
    $db = new LekTrail\WordPress\Database();
    $installer = new LekTrail\TableInstaller($db);
    $installer->createTable();
});

spl_autoload_register(function ($class) {
    $prefix = 'LekTrail\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $file = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

add_action('init', function () {
    $locale = determine_locale();
    $localeFile = __DIR__ . '/languages/lektrail-reading-tracker-' . $locale . '.mo';
    $fallbackFile = __DIR__ . '/languages/lektrail-reading-tracker-es_ES.mo';

    if (file_exists($localeFile)) {
        load_textdomain('lektrail-reading-tracker', $localeFile);
    } elseif (strpos($locale, 'es_') === 0 && file_exists($fallbackFile)) {
        load_textdomain('lektrail-reading-tracker', $fallbackFile);
    }
});

add_action('plugins_loaded', function () {
    $pluginPath = plugin_dir_path(__FILE__);
    $pluginUrl = plugin_dir_url(__FILE__);
    $version = '1.0.0';

    $scripts = new LekTrail\WordPress\ScriptLoader();
    $options = new LekTrail\WordPress\Options();
    $hooks = new LekTrail\WordPress\Hooks();
    $context = new LekTrail\WordPress\Context();
    $locale = new LekTrail\WordPress\Locale();

    $pluginConfigs = new LekTrail\WordPress\PluginConfigRepository($options, $locale);
    $posts = new LekTrail\WordPress\PostQuery();
    $assets = new LekTrail\Assets($scripts, $pluginPath, $pluginUrl, $version, $pluginConfigs, $posts);
    $response = new LekTrail\WordPress\JsonResponse();

    $db = new LekTrail\WordPress\Database();
    $users = new LekTrail\WordPress\UserProvider();
    $trackings = new LekTrail\WordPress\TrackingRepository($db);
    $trackingService = new LekTrail\TrackingService($users, $trackings, $pluginConfigs);

    $suggestionsQuery = new LekTrail\SuggestionsQuery($pluginConfigs, $posts);
    $suggestions = new LekTrail\SuggestionsEndpoint($suggestionsQuery, $response);
    $previewEndpoint = new LekTrail\PreviewEndpoint($posts);
    $widgetRenderer = new LekTrail\WidgetRenderer($assets, $trackingService, $suggestionsQuery, $posts, $pluginConfigs);
    $shortcode = new LekTrail\Shortcodes\WidgetShortcode($widgetRenderer);
    $adminPage = new LekTrail\AdminPage($pluginConfigs);
    $nonceVerifier = new LekTrail\WordPress\NonceVerifier();
    $trackingEndpoint = new LekTrail\TrackingEndpoint($trackingService, $response, $nonceVerifier);

    $widgetBlock = new LekTrail\Blocks\Widget\WidgetBlock($widgetRenderer, $assets, $pluginPath);
    $widgetBlock->register($hooks);

    $transients = new LekTrail\WordPress\Transients();
    $readingHistory = new LekTrail\Dashboard\ReadingHistoryRepository($db);
    $postCounts = new LekTrail\Dashboard\PostCountRepository($db, $transients);
    $cacheInvalidator = new LekTrail\Dashboard\CacheInvalidator($postCounts);
    $cacheInvalidator->register($hooks);

    $categoryResolver = new LekTrail\WordPress\CategoryResolver();
    $progressRenderer = new LekTrail\Renderers\ProgressRenderer($readingHistory, $postCounts, $users, $assets, $categoryResolver);
    $readingListRenderer = new LekTrail\Renderers\ReadingListRenderer($readingHistory, $posts, $users, $assets, $categoryResolver);

    $progressShortcode = new LekTrail\Shortcodes\ProgressShortcode($progressRenderer);
    $progressShortcode->register($hooks);
    $readingListShortcode = new LekTrail\Shortcodes\ReadingListShortcode($readingListRenderer);
    $readingListShortcode->register($hooks);

    $progressBlock = new LekTrail\Blocks\Progress\ProgressBlock($progressRenderer, $pluginPath);
    $progressBlock->register($hooks);
    $readingListBlock = new LekTrail\Blocks\ReadingList\ReadingListBlock($readingListRenderer, $pluginPath);
    $readingListBlock->register($hooks);

    $plugin = new LekTrail\Plugin($assets, $pluginConfigs, $context, $suggestions, $previewEndpoint, $shortcode, $adminPage, $hooks, $trackingService, $trackingEndpoint);
    $plugin->run();
});
