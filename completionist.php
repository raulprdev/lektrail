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
    $scripts = new Completionist\WordPressScriptLoader();
    $options = new Completionist\WordPressOptions();
    $hooks = new Completionist\WordPressHooks();
    $context = new Completionist\WordPressContext();
    $locale = new Completionist\WordPressLocale();

    $settingsRepo = new Completionist\WordPressSettingsRepository($options, $locale);
    $settings = $settingsRepo->load();
    $posts = new Completionist\WordPressPostQuery();
    $assets = new Completionist\Assets($scripts, COMPLETIONIST_PLUGIN_PATH, COMPLETIONIST_PLUGIN_URL, COMPLETIONIST_VERSION, $settings, $posts);
    $response = new Completionist\WordPressJsonResponse();

    $suggestions = new Completionist\SuggestionsEndpoint($posts, $response);
    $shortcode = new Completionist\Shortcode($assets);
    $adminPage = new Completionist\AdminPage($settingsRepo);

    $plugin = new Completionist\Plugin($assets, $settingsRepo, $context, $suggestions, $shortcode, $adminPage, $hooks);
    $plugin->run();
});