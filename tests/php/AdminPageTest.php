<?php

namespace LekTrail\Tests;

use LekTrail\AdminPage;
use LekTrail\Tests\Mocks\MockHooks;
use LekTrail\Tests\Mocks\MockPluginConfigRepository;
use PHPUnit\Framework\TestCase;

class AdminPageTest extends TestCase
{
    public function testRegistersAdminMenuHook(): void
    {
        $hooks = new MockHooks();
        $pluginConfigs = new MockPluginConfigRepository();
        $page = new AdminPage($pluginConfigs);

        $page->register($hooks);

        $this->assertArrayHasKey('admin_menu', $hooks->actions);
    }

    public function testRegistersAdminInitHook(): void
    {
        $hooks = new MockHooks();
        $pluginConfigs = new MockPluginConfigRepository();
        $page = new AdminPage($pluginConfigs);

        $page->register($hooks);

        $this->assertArrayHasKey('admin_init', $hooks->actions);
    }

    public function testGetSettingsReturnsFromRepository(): void
    {
        $pluginConfigs = new MockPluginConfigRepository([ 'max_viewed' => 12]);
        $page = new AdminPage($pluginConfigs);

        $pluginConfig = $page->getPluginConfig();

        $this->assertEquals(12, $pluginConfig->maxViewed());
    }
}
