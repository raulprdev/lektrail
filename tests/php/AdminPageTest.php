<?php

namespace Completionist\Tests;

use Completionist\AdminPage;
use Completionist\Settings;
use Completionist\Tests\Mocks\MockHooks;
use Completionist\Tests\Mocks\MockSettingsRepository;
use PHPUnit\Framework\TestCase;

class AdminPageTest extends TestCase {

    public function testRegistersAdminMenuHook(): void {
        $hooks = new MockHooks();
        $repository = new MockSettingsRepository();
        $page = new AdminPage($repository);

        $page->register($hooks);

        $this->assertArrayHasKey('admin_menu', $hooks->actions);
    }

    public function testRegistersAdminInitHook(): void {
        $hooks = new MockHooks();
        $repository = new MockSettingsRepository();
        $page = new AdminPage($repository);

        $page->register($hooks);

        $this->assertArrayHasKey('admin_init', $hooks->actions);
    }

    public function testGetSettingsReturnsFromRepository(): void {
        $repository = new MockSettingsRepository();
        $repository->settings = Settings::fromArray(['max_viewed' => 12]);
        $page = new AdminPage($repository);

        $settings = $page->getSettings();

        $this->assertEquals(12, $settings->maxViewed());
    }
}