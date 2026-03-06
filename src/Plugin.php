<?php

namespace Completionist;

class Plugin {

    private Hooks $hooks;
    private Assets $assets;
    private PostQuery $posts;
    private JsonResponse $response;
    private SuggestionsEndpoint $suggestions;
    private Shortcode $shortcode;
    private SettingsRepository $settings;
    private AdminPage $adminPage;

    public function __construct(string $pluginPath, string $pluginUrl, string $version) {
        $this->hooks = new WordPressHooks();
        $scripts = new WordPressScriptLoader();
        $options = new WordPressOptions();

        $this->assets = new Assets($scripts, $pluginPath, $pluginUrl, $version);
        $this->posts = new WordPressPostQuery();
        $this->response = new WordPressJsonResponse();
        $this->suggestions = new SuggestionsEndpoint($this->posts, $this->response);
        $this->shortcode = new Shortcode($this->assets);
        $this->settings = new WordPressSettingsRepository($options);
        $this->adminPage = new AdminPage($this->settings);
    }

    public function run(): void {
        $this->suggestions->register($this->hooks);
        $this->shortcode->register($this->hooks);
        $this->adminPage->register($this->hooks);
        $this->hooks->addAction('wp_enqueue_scripts', [$this, 'maybeEnqueueDetector']);
    }

    public function maybeEnqueueDetector(): void {
        if (!is_singular('post')) {
            return;
        }
        $this->assets->enqueueDetector(get_the_ID());
    }
}