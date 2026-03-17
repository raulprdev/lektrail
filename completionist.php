<?php
/**
 * Plugin Name:       Completionist
 * Description:       Track reading progress and suggest unread posts.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            raulprdev
 * License:           GPL v2 or later
 * Text Domain:       completionist
 */

defined('ABSPATH') || exit;

register_activation_hook(__FILE__, function () {
    $db = new Completionist\WordPress\Database();
    $installer = new Completionist\TableInstaller($db);
    $installer->createTable();
});

spl_autoload_register(function ($class) {
    $prefix = 'Completionist\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $file = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

add_action('init', function () {
    $languagesPath = dirname(plugin_basename(__FILE__)) . '/languages';
    $loaded = load_plugin_textdomain('completionist', false, $languagesPath);

    $locale = determine_locale();
    $isSpanishVariant = strpos($locale, 'es_') === 0 && $locale !== 'es_ES';

    if (!$loaded && $isSpanishVariant) {
        $fallbackFile = __DIR__ . '/languages/completionist-es_ES.mo';
        load_textdomain('completionist', $fallbackFile);
    }
});

add_action('plugins_loaded', function () {
    $pluginPath = plugin_dir_path(__FILE__);
    $pluginUrl = plugin_dir_url(__FILE__);
    $version = '1.0.0';

    $scripts = new Completionist\WordPress\ScriptLoader();
    $options = new Completionist\WordPress\Options();
    $hooks = new Completionist\WordPress\Hooks();
    $context = new Completionist\WordPress\Context();
    $locale = new Completionist\WordPress\Locale();

    $pluginConfigs = new Completionist\WordPress\PluginConfigRepository($options, $locale);
    $posts = new Completionist\WordPress\PostQuery();
    $assets = new Completionist\Assets($scripts, $pluginPath, $pluginUrl, $version, $pluginConfigs, $posts);
    $response = new Completionist\WordPress\JsonResponse();

    $db = new Completionist\WordPress\Database();
    $users = new Completionist\WordPress\UserProvider();
    $trackings = new Completionist\WordPress\TrackingRepository($db);
    $trackingService = new Completionist\TrackingService($users, $trackings, $pluginConfigs);

    $suggestionsQuery = new Completionist\SuggestionsQuery($pluginConfigs, $posts);
    $suggestions = new Completionist\SuggestionsEndpoint($suggestionsQuery, $response);
    $previewEndpoint = new Completionist\PreviewEndpoint($posts);
    $widgetRenderer = new Completionist\WidgetRenderer($assets, $trackingService, $suggestionsQuery, $posts, $pluginConfigs);
    $shortcode = new Completionist\Shortcodes\WidgetShortcode($widgetRenderer);
    $adminPage = new Completionist\AdminPage($pluginConfigs);
    $nonceVerifier = new Completionist\WordPress\NonceVerifier();
    $trackingEndpoint = new Completionist\TrackingEndpoint($trackingService, $response, $nonceVerifier);

    $widgetBlock = new Completionist\Blocks\Widget\WidgetBlock($widgetRenderer, $assets, $pluginPath);
    $widgetBlock->register($hooks);

    $plugin = new Completionist\Plugin($assets, $pluginConfigs, $context, $suggestions, $previewEndpoint, $shortcode, $adminPage, $hooks, $trackingService, $trackingEndpoint);
    $plugin->run();
});
