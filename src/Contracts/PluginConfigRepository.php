<?php

namespace Completionist\Contracts;

use Completionist\PluginConfig;

interface PluginConfigRepository
{
    public function load(): PluginConfig;
    public function save(PluginConfig $pluginConfig): void;
}
