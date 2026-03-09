<?php

namespace Completionist\Tests;

use Completionist\Settings;
use Completionist\WordPressSettingsRepository;
use Completionist\Tests\Mocks\MockLocale;
use Completionist\Tests\Mocks\MockOptions;
use PHPUnit\Framework\TestCase;

class SettingsRepositoryTest extends TestCase {

    private MockOptions $options;
    private MockLocale $locale;

    protected function setUp(): void {
        $this->options = new MockOptions();
        $this->locale = new MockLocale();
    }

    public function testLoadReturnsDefaultsWhenNoSavedSettings(): void {
        $repository = new WordPressSettingsRepository($this->options, $this->locale);

        $settings = $repository->load();

        $this->assertEquals(Settings::DEFAULT_POST_TYPES, $settings->postTypes());
    }

    public function testLoadReturnsSavedSettings(): void {
        $this->options->set(WordPressSettingsRepository::OPTION_KEY, [
            'post_types' => ['post', 'page'],
            'max_viewed' => 10,
        ]);
        $repository = new WordPressSettingsRepository($this->options, $this->locale);

        $settings = $repository->load();

        $this->assertEquals(['post', 'page'], $settings->postTypes());
        $this->assertEquals(10, $settings->maxViewed());
    }

    public function testSaveStoresSettings(): void {
        $repository = new WordPressSettingsRepository($this->options, $this->locale);
        $settings = Settings::fromArray(['max_viewed' => 7]);

        $repository->save($settings);

        $saved = $this->options->get(WordPressSettingsRepository::OPTION_KEY);
        $this->assertEquals(7, $saved['max_viewed']);
    }

    public function testLoadReturnsEnglishLabelsForEnglishLocale(): void {
        $this->locale->code = 'en_US';
        $repository = new WordPressSettingsRepository($this->options, $this->locale);

        $settings = $repository->load();

        $this->assertEquals('Continue reading', $settings->labelContinue());
        $this->assertEquals('Completed', $settings->labelCompleted());
        $this->assertEquals('Suggested reading', $settings->labelSuggestions());
    }

    public function testLoadReturnsSpanishLabelsForSpanishLocale(): void {
        $this->locale->code = 'es_ES';
        $repository = new WordPressSettingsRepository($this->options, $this->locale);

        $settings = $repository->load();

        $this->assertEquals('Seguir leyendo', $settings->labelContinue());
        $this->assertEquals('Completados', $settings->labelCompleted());
        $this->assertEquals('Lecturas sugeridas', $settings->labelSuggestions());
    }

    public function testLoadReturnsSpanishLabelsForArgentineLocale(): void {
        $this->locale->code = 'es_AR';
        $repository = new WordPressSettingsRepository($this->options, $this->locale);

        $settings = $repository->load();

        $this->assertEquals('Seguir leyendo', $settings->labelContinue());
    }

    public function testSavedLabelsOverrideLocaleDefaults(): void {
        $this->locale->code = 'es_ES';
        $this->options->set(WordPressSettingsRepository::OPTION_KEY, [
            'label_continue' => 'Custom label',
        ]);
        $repository = new WordPressSettingsRepository($this->options, $this->locale);

        $settings = $repository->load();

        $this->assertEquals('Custom label', $settings->labelContinue());
    }

    public function testLoadReturnsSpanishConsentLabelsForSpanishLocale(): void {
        $this->locale->code = 'es_ES';
        $repository = new WordPressSettingsRepository($this->options, $this->locale);

        $settings = $repository->load();

        $this->assertEquals('¿Seguir tu progreso de lectura en este sitio?', $settings->consentMessage());
        $this->assertEquals('Sí, seguir mi lectura', $settings->consentCheckboxLabel());
    }
}