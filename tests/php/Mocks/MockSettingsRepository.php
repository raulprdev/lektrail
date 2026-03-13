<?php

namespace Completionist\Tests\Mocks;

use Completionist\Contracts\SettingsRepository;
use Completionist\Settings;

class MockSettingsRepository implements SettingsRepository
{
    private Settings $settings;
    public bool $saveCalled = false;

    public function __construct(array $config = [])
    {
        $this->settings = Settings::fromArray($config);
    }

    public function load(): Settings
    {
        return $this->settings;
    }

    public function save(Settings $settings): void
    {
        $this->settings = $settings;
        $this->saveCalled = true;
    }
}
