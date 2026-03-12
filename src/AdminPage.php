<?php

namespace Completionist;

use Completionist\Contracts\Hooks;
use Completionist\Contracts\SettingsRepository;
use Completionist\WordPress\SettingsRepository as WordPressSettingsRepository;

class AdminPage {

    public const MENU_SLUG = 'completionist';

    private SettingsRepository $repository;

    public function __construct(SettingsRepository $repository) {
        $this->repository = $repository;
    }

    public function register(Hooks $hooks): void {
        $hooks->addAction('admin_menu', [$this, 'addMenu']);
        $hooks->addAction('admin_init', [$this, 'registerSettings']);
    }

    public function addMenu(): void {
        add_options_page(
            'Completionist',
            'Completionist',
            'manage_options',
            self::MENU_SLUG,
            [$this, 'render']
        );
    }

    public function registerSettings(): void {
        register_setting(self::MENU_SLUG, WordPressSettingsRepository::OPTION_KEY);
    }

    public function getSettings(): Settings {
        return $this->repository->load();
    }

    public function render(): void {
        $settings = $this->getSettings();
        include dirname(__DIR__) . '/templates/admin-page.php';
    }
}