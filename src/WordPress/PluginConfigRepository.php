<?php

namespace Completionist\WordPress;

use Completionist\Contracts\Locale as LocaleInterface;
use Completionist\Contracts\Options as OptionsInterface;
use Completionist\Contracts\PluginConfigRepository as SettingsRepositoryInterface;
use Completionist\PluginConfig;

class PluginConfigRepository implements SettingsRepositoryInterface
{
    public const OPTION_KEY = 'completionist_settings';

    private const LABELS_ES = [
        'label_continue' => 'Seguir leyendo',
        'label_completed' => 'Completados',
        'label_suggestions' => 'Lecturas sugeridas',
        'label_empty' => '¡Empieza a leer para seguir tu progreso!',
        'label_loading' => 'Cargando sugerencias...',
        'consent_message' => '¿Seguir tu progreso de lectura en este sitio?',
        'consent_checkbox_label' => 'Sí, seguir mi lectura',
        'label_clear' => 'Borrar historial',
    ];

    private OptionsInterface $options;
    private LocaleInterface $locale;
    private ?PluginConfig $cached = null;

    public function __construct(OptionsInterface $options, LocaleInterface $locale)
    {
        $this->options = $options;
        $this->locale = $locale;
    }

    public function load(): PluginConfig
    {
        if ($this->cached !== null) {
            return $this->cached;
        }
        $data = $this->options->get(self::OPTION_KEY, []);
        $defaults = $this->getLocaleDefaults();
        $this->cached = PluginConfig::fromArray($data, $defaults);
        return $this->cached;
    }

    public function save(PluginConfig $pluginConfig): void
    {
        $this->options->set(self::OPTION_KEY, $pluginConfig->toArray());
        $this->cached = $pluginConfig;
    }

    private function getLocaleDefaults(): array
    {
        if (str_starts_with($this->locale->getCode(), 'es')) {
            return self::LABELS_ES;
        }
        return [];
    }
}
