<?php

namespace LekTrail\Contracts;

use LekTrail\PluginConfig;

interface PluginConfigRepository
{
    public function load(): PluginConfig;
    public function save(PluginConfig $pluginConfig): void;
}
