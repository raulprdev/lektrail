<?php

namespace Completionist\Tests;

use Completionist\PluginConfig;
use Completionist\Tests\Mocks\MockLocale;
use Completionist\Tests\Mocks\MockOptions;
use Completionist\WordPress\PluginConfigRepository;
use PHPUnit\Framework\TestCase;

class PluginConfigRepositoryTest extends TestCase
{
    private MockOptions $options;
    private MockLocale $locale;

    protected function setUp(): void
    {
        $this->options = new MockOptions();
        $this->locale = new MockLocale();
    }

    public function testLoadReturnsDefaultsWhenNoSavedSettings(): void
    {
        $pluginConfigs = new PluginConfigRepository($this->options, $this->locale);

        $pluginConfig = $pluginConfigs->load();

        $this->assertEquals(PluginConfig::DEFAULT_POST_TYPES, $pluginConfig->postTypes());
    }

    public function testLoadReturnsSavedSettings(): void
    {
        $this->options->set(PluginConfigRepository::OPTION_KEY, [
            'post_types' => ['post', 'page'],
            'max_viewed' => 10,
        ]);
        $pluginConfigs = new PluginConfigRepository($this->options, $this->locale);

        $pluginConfig = $pluginConfigs->load();

        $this->assertEquals(['post', 'page'], $pluginConfig->postTypes());
        $this->assertEquals(10, $pluginConfig->maxViewed());
    }

    public function testSaveStoresSettings(): void
    {
        $pluginConfigs = new PluginConfigRepository($this->options, $this->locale);
        $pluginConfig = PluginConfig::fromArray([ 'max_viewed' => 7]);

        $pluginConfigs->save($pluginConfig);

        $saved = $this->options->get(PluginConfigRepository::OPTION_KEY);
        $this->assertEquals(7, $saved['max_viewed']);
    }

    public function testLoadReturnsEnglishLabelsForEnglishLocale(): void
    {
        $this->locale->code = 'en_US';
        $pluginConfigs = new PluginConfigRepository($this->options, $this->locale);

        $pluginConfig = $pluginConfigs->load();

        $this->assertEquals('Continue reading', $pluginConfig->labelContinue());
        $this->assertEquals('Completed', $pluginConfig->labelCompleted());
        $this->assertEquals('Suggested reading', $pluginConfig->labelSuggestions());
    }

    public function testLoadReturnsSpanishLabelsForSpanishLocale(): void
    {
        $this->locale->code = 'es_ES';
        $pluginConfigs = new PluginConfigRepository($this->options, $this->locale);

        $pluginConfig = $pluginConfigs->load();

        $this->assertEquals('Seguir leyendo', $pluginConfig->labelContinue());
        $this->assertEquals('Completados', $pluginConfig->labelCompleted());
        $this->assertEquals('Lecturas sugeridas', $pluginConfig->labelSuggestions());
    }

    public function testLoadReturnsSpanishLabelsForArgentineLocale(): void
    {
        $this->locale->code = 'es_AR';
        $pluginConfigs = new PluginConfigRepository($this->options, $this->locale);

        $pluginConfig = $pluginConfigs->load();

        $this->assertEquals('Seguir leyendo', $pluginConfig->labelContinue());
    }

    public function testSavedLabelsOverrideLocaleDefaults(): void
    {
        $this->locale->code = 'es_ES';
        $this->options->set(PluginConfigRepository::OPTION_KEY, [
            'label_continue' => 'Custom label',
        ]);
        $pluginConfigs = new PluginConfigRepository($this->options, $this->locale);

        $pluginConfig = $pluginConfigs->load();

        $this->assertEquals('Custom label', $pluginConfig->labelContinue());
    }

    public function testLoadReturnsSpanishConsentLabelsForSpanishLocale(): void
    {
        $this->locale->code = 'es_ES';
        $pluginConfigs = new PluginConfigRepository($this->options, $this->locale);

        $pluginConfig = $pluginConfigs->load();

        $this->assertEquals('¿Seguir tu progreso de lectura en este sitio?', $pluginConfig->consentMessage());
        $this->assertEquals('Sí, seguir mi lectura', $pluginConfig->consentCheckboxLabel());
    }
}
