<?php

namespace Completionist\Contracts;

use Completionist\Settings;

interface SettingsRepository
{
    public function load(): Settings;
    public function save(Settings $settings): void;
}
