<?php

namespace Completionist;

use Completionist\Contracts\Hooks;
use Completionist\Contracts\PluginConfigRepository;
use Completionist\WordPress\PluginConfigRepository as WordPressSettingsRepository;

class AdminPage
{
    public const MENU_SLUG = 'completionist';

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
            'Completionist',
            'Completionist',
            'manage_options',
            self::MENU_SLUG,
            [$this, 'render']
        );
    }

    public function registerSettings(): void
    {
        register_setting(self::MENU_SLUG, WordPressSettingsRepository::OPTION_KEY);
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
