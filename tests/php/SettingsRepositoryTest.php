<?php

namespace Completionist\Tests;

use Completionist\Settings;
use Completionist\WordPressSettingsRepository;
use Completionist\Tests\Mocks\MockOptions;
use PHPUnit\Framework\TestCase;

class SettingsRepositoryTest extends TestCase {

    public function testLoadReturnsDefaultsWhenNoSavedSettings(): void {
        $options = new MockOptions();
        $repository = new WordPressSettingsRepository($options);

        $settings = $repository->load();

        $this->assertEquals(Settings::DEFAULT_POST_TYPES, $settings->postTypes());
    }

    public function testLoadReturnsSavedSettings(): void {
        $options = new MockOptions();
        $options->set(WordPressSettingsRepository::OPTION_KEY, [
            'post_types' => ['post', 'page'],
            'max_viewed' => 10,
        ]);
        $repository = new WordPressSettingsRepository($options);

        $settings = $repository->load();

        $this->assertEquals(['post', 'page'], $settings->postTypes());
        $this->assertEquals(10, $settings->maxViewed());
    }

    public function testSaveStoresSettings(): void {
        $options = new MockOptions();
        $repository = new WordPressSettingsRepository($options);
        $settings = Settings::fromArray(['max_viewed' => 7]);

        $repository->save($settings);

        $saved = $options->get(WordPressSettingsRepository::OPTION_KEY);
        $this->assertEquals(7, $saved['max_viewed']);
    }
}