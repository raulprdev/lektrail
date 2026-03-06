<?php

namespace Completionist;

interface SettingsRepository {
    public function load(): Settings;
    public function save(Settings $settings): void;
}