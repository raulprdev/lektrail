<?php
/**
 * Plugin Name:       Completionist
 * Description:       Track reading progress and suggest unread posts.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Completionist
 * License:           GPL v2 or later
 * Text Domain:       completionist
 */

defined('ABSPATH') || exit;

define('COMPLETIONIST_VERSION', '1.0.0');
define('COMPLETIONIST_PLUGIN_URL', plugin_dir_url(__FILE__));

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

define('COMPLETIONIST_PLUGIN_PATH', plugin_dir_path(__FILE__));

add_action('plugins_loaded', function () {
    $scripts = new Completionist\WordPress\ScriptLoader();
    $options = new Completionist\WordPress\Options();
    $hooks = new Completionist\WordPress\Hooks();
    $context = new Completionist\WordPress\Context();
    $locale = new Completionist\WordPress\Locale();

    $settingsRepo = new Completionist\WordPress\SettingsRepository($options, $locale);
    $posts = new Completionist\WordPress\PostQuery();
    $assets = new Completionist\Assets($scripts, COMPLETIONIST_PLUGIN_PATH, COMPLETIONIST_PLUGIN_URL, COMPLETIONIST_VERSION, $settingsRepo, $posts);
    $response = new Completionist\WordPress\JsonResponse();

    $db = new Completionist\WordPress\Database();
    $users = new Completionist\WordPress\UserProvider();
    $trackingRepo = new Completionist\WordPress\TrackingRepository($db);
    $trackingService = new Completionist\TrackingService($users, $trackingRepo, $settingsRepo);

    $suggestionsQuery = new Completionist\SuggestionsQuery($settingsRepo, $posts);
    $suggestions = new Completionist\SuggestionsEndpoint($suggestionsQuery, $response);
    $shortcode = new Completionist\Shortcodes\WidgetShortcode($assets, $trackingService, $suggestionsQuery, $posts);
    $adminPage = new Completionist\AdminPage($settingsRepo);
    $trackingEndpoint = new Completionist\TrackingEndpoint($trackingService, $response);

    $plugin = new Completionist\Plugin($assets, $settingsRepo, $context, $suggestions, $shortcode, $adminPage, $hooks, $trackingService, $trackingEndpoint);
    $plugin->run();
});