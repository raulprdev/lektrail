<?php

namespace Completionist;

class WordPressSettingsRepository implements SettingsRepository {

    public const OPTION_KEY = 'completionist_settings';

    private const LABELS_ES = [
        'label_continue' => 'Seguir leyendo',
        'label_completed' => 'Completados',
        'label_suggestions' => 'Lecturas sugeridas',
        'label_empty' => '¡Empieza a leer para seguir tu progreso!',
        'label_loading' => 'Cargando sugerencias...',
        'consent_message' => '¿Seguir tu progreso de lectura en este sitio?',
        'consent_checkbox_label' => 'Sí, seguir mi lectura',
    ];

    private Options $options;
    private Locale $locale;

    public function __construct(Options $options, Locale $locale) {
        $this->options = $options;
        $this->locale = $locale;
    }

    public function load(): Settings {
        $data = $this->options->get(self::OPTION_KEY, []);
        $defaults = $this->getLocaleDefaults();
        return Settings::fromArray($data, $defaults);
    }

    public function save(Settings $settings): void {
        $this->options->set(self::OPTION_KEY, $settings->toArray());
    }

    private function getLocaleDefaults(): array {
        if (str_starts_with($this->locale->getCode(), 'es')) {
            return self::LABELS_ES;
        }
        return [];
    }
}
