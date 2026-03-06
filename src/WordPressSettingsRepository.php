<?php

namespace Completionist;

class WordPressSettingsRepository implements SettingsRepository {

    public const OPTION_KEY = 'completionist_settings';

    private Options $options;

    public function __construct(Options $options) {
        $this->options = $options;
    }

    public function load(): Settings {
        $data = $this->options->get(self::OPTION_KEY, []);
        return Settings::fromArray($data);
    }

    public function save(Settings $settings): void {
        $this->options->set(self::OPTION_KEY, $settings->toArray());
    }
}