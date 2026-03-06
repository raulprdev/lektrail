<?php

namespace Completionist\Tests\Mocks;

use Completionist\Settings;
use Completionist\SettingsRepository;

class MockSettingsRepository implements SettingsRepository {

    public Settings $settings;
    public bool $saveCalled = false;

    public function __construct() {
        $this->settings = new Settings();
    }

    public function load(): Settings {
        return $this->settings;
    }

    public function save(Settings $settings): void {
        $this->settings = $settings;
        $this->saveCalled = true;
    }
}