<?php

namespace Completionist\Tests\Mocks;

use Completionist\Contracts\PluginConfigRepository;
use Completionist\PluginConfig;

class MockPluginConfigRepository implements PluginConfigRepository
{
    private PluginConfig $pluginConfig;
    public bool $saveCalled = false;

    public function __construct(array $config = [])
    {
        $this->pluginConfig = PluginConfig::fromArray($config);
    }

    public function load(): PluginConfig
    {
        return $this->pluginConfig;
    }

    public function save(PluginConfig $pluginConfig): void
    {
        $this->pluginConfig = $pluginConfig;
        $this->saveCalled   = true;
    }
}
