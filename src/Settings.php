<?php

namespace Completionist;

class Settings {

    public const DEFAULT_POST_TYPES = ['post'];
    public const DEFAULT_MAX_VIEWED = 3;
    public const DEFAULT_MAX_READ = 5;
    public const DEFAULT_MAX_SUGGESTIONS = 5;
    public const DEFAULT_LABEL_CONTINUE = 'Continue reading';
    public const DEFAULT_LABEL_COMPLETED = 'Completed';
    public const DEFAULT_LABEL_SUGGESTIONS = 'Suggested reading';
    public const DEFAULT_LABEL_EMPTY = 'Start reading to track your progress!';
    public const DEFAULT_LABEL_LOADING = 'Loading suggestions...';
    public const DEFAULT_VIEWED_ENABLED = true;
    public const DEFAULT_COMPLETED_ENABLED = true;
    public const DEFAULT_REQUIRE_CONSENT = false;
    public const DEFAULT_CONSENT_MESSAGE = 'Track your reading progress on this site?';
    public const DEFAULT_CONSENT_CHECKBOX_LABEL = 'Yes, track my reading';
    public const DEFAULT_SUGGESTIONS_CACHE_HOURS = 24;
    public const DEFAULT_SHOW_EXCERPT = false;
    public const DEFAULT_SHOW_THUMBNAIL = false;
    public const DEFAULT_EXCERPT_LENGTH = 20;
    public const DEFAULT_READ_THRESHOLD = 90;
    public const DEFAULT_SUGGESTION_ORDER = 'random';
    public const DEFAULT_INCLUDE_CATEGORIES = [];
    public const DEFAULT_EXCLUDE_CATEGORIES = [];
    public const DEFAULT_SHOW_CLEAR_BUTTON = true;
    public const DEFAULT_LABEL_CLEAR = 'Clear history';

    private array $postTypes;
    private int $maxViewed;
    private int $maxRead;
    private int $maxSuggestions;
    private string $labelContinue;
    private string $labelCompleted;
    private string $labelSuggestions;
    private string $labelEmpty;
    private string $labelLoading;
    private bool $viewedEnabled;
    private bool $completedEnabled;
    private bool $requireConsent;
    private string $consentMessage;
    private string $consentCheckboxLabel;
    private int $suggestionsCacheHours;
    private bool $showExcerpt;
    private bool $showThumbnail;
    private int $excerptLength;
    private int $readThreshold;
    private string $suggestionOrder;
    private array $includeCategories;
    private array $excludeCategories;
    private bool $showClearButton;
    private string $labelClear;

    public function __construct(
        array $postTypes = self::DEFAULT_POST_TYPES,
        int $maxViewed = self::DEFAULT_MAX_VIEWED,
        int $maxRead = self::DEFAULT_MAX_READ,
        int $maxSuggestions = self::DEFAULT_MAX_SUGGESTIONS,
        string $labelContinue = self::DEFAULT_LABEL_CONTINUE,
        string $labelCompleted = self::DEFAULT_LABEL_COMPLETED,
        string $labelSuggestions = self::DEFAULT_LABEL_SUGGESTIONS,
        string $labelEmpty = self::DEFAULT_LABEL_EMPTY,
        string $labelLoading = self::DEFAULT_LABEL_LOADING,
        bool $viewedEnabled = self::DEFAULT_VIEWED_ENABLED,
        bool $completedEnabled = self::DEFAULT_COMPLETED_ENABLED,
        bool $requireConsent = self::DEFAULT_REQUIRE_CONSENT,
        string $consentMessage = self::DEFAULT_CONSENT_MESSAGE,
        string $consentCheckboxLabel = self::DEFAULT_CONSENT_CHECKBOX_LABEL,
        int $suggestionsCacheHours = self::DEFAULT_SUGGESTIONS_CACHE_HOURS,
        bool $showExcerpt = self::DEFAULT_SHOW_EXCERPT,
        bool $showThumbnail = self::DEFAULT_SHOW_THUMBNAIL,
        int $excerptLength = self::DEFAULT_EXCERPT_LENGTH,
        int $readThreshold = self::DEFAULT_READ_THRESHOLD,
        string $suggestionOrder = self::DEFAULT_SUGGESTION_ORDER,
        array $includeCategories = self::DEFAULT_INCLUDE_CATEGORIES,
        array $excludeCategories = self::DEFAULT_EXCLUDE_CATEGORIES,
        bool $showClearButton = self::DEFAULT_SHOW_CLEAR_BUTTON,
        string $labelClear = self::DEFAULT_LABEL_CLEAR
    ) {
        $this->postTypes = $postTypes;
        $this->maxViewed = $maxViewed;
        $this->maxRead = $maxRead;
        $this->maxSuggestions = $maxSuggestions;
        $this->labelContinue = $labelContinue;
        $this->labelCompleted = $labelCompleted;
        $this->labelSuggestions = $labelSuggestions;
        $this->labelEmpty = $labelEmpty;
        $this->labelLoading = $labelLoading;
        $this->viewedEnabled = $viewedEnabled;
        $this->completedEnabled = $completedEnabled;
        $this->requireConsent = $requireConsent;
        $this->consentMessage = $consentMessage;
        $this->consentCheckboxLabel = $consentCheckboxLabel;
        $this->suggestionsCacheHours = $suggestionsCacheHours;
        $this->showExcerpt = $showExcerpt;
        $this->showThumbnail = $showThumbnail;
        $this->excerptLength = $excerptLength;
        $this->readThreshold = $readThreshold;
        $this->suggestionOrder = $suggestionOrder;
        $this->includeCategories = $includeCategories;
        $this->excludeCategories = $excludeCategories;
        $this->showClearButton = $showClearButton;
        $this->labelClear = $labelClear;
    }

    public static function fromArray(array $data, array $defaults = []): self {
        return new self(
            $data['post_types'] ?? $defaults['post_types'] ?? self::DEFAULT_POST_TYPES,
            $data['max_viewed'] ?? $defaults['max_viewed'] ?? self::DEFAULT_MAX_VIEWED,
            $data['max_read'] ?? $defaults['max_read'] ?? self::DEFAULT_MAX_READ,
            $data['max_suggestions'] ?? $defaults['max_suggestions'] ?? self::DEFAULT_MAX_SUGGESTIONS,
            $data['label_continue'] ?? $defaults['label_continue'] ?? self::DEFAULT_LABEL_CONTINUE,
            $data['label_completed'] ?? $defaults['label_completed'] ?? self::DEFAULT_LABEL_COMPLETED,
            $data['label_suggestions'] ?? $defaults['label_suggestions'] ?? self::DEFAULT_LABEL_SUGGESTIONS,
            $data['label_empty'] ?? $defaults['label_empty'] ?? self::DEFAULT_LABEL_EMPTY,
            $data['label_loading'] ?? $defaults['label_loading'] ?? self::DEFAULT_LABEL_LOADING,
            $data['viewed_enabled'] ?? $defaults['viewed_enabled'] ?? self::DEFAULT_VIEWED_ENABLED,
            $data['completed_enabled'] ?? $defaults['completed_enabled'] ?? self::DEFAULT_COMPLETED_ENABLED,
            (bool) ($data['require_consent'] ?? $defaults['require_consent'] ?? self::DEFAULT_REQUIRE_CONSENT),
            $data['consent_message'] ?? $defaults['consent_message'] ?? self::DEFAULT_CONSENT_MESSAGE,
            $data['consent_checkbox_label'] ?? $defaults['consent_checkbox_label'] ?? self::DEFAULT_CONSENT_CHECKBOX_LABEL,
            (int) ($data['suggestions_cache_hours'] ?? $defaults['suggestions_cache_hours'] ?? self::DEFAULT_SUGGESTIONS_CACHE_HOURS),
            (bool) ($data['show_excerpt'] ?? $defaults['show_excerpt'] ?? self::DEFAULT_SHOW_EXCERPT),
            (bool) ($data['show_thumbnail'] ?? $defaults['show_thumbnail'] ?? self::DEFAULT_SHOW_THUMBNAIL),
            (int) ($data['excerpt_length'] ?? $defaults['excerpt_length'] ?? self::DEFAULT_EXCERPT_LENGTH),
            (int) ($data['read_threshold'] ?? $defaults['read_threshold'] ?? self::DEFAULT_READ_THRESHOLD),
            $data['suggestion_order'] ?? $defaults['suggestion_order'] ?? self::DEFAULT_SUGGESTION_ORDER,
            $data['include_categories'] ?? $defaults['include_categories'] ?? self::DEFAULT_INCLUDE_CATEGORIES,
            $data['exclude_categories'] ?? $defaults['exclude_categories'] ?? self::DEFAULT_EXCLUDE_CATEGORIES,
            (bool) ($data['show_clear_button'] ?? $defaults['show_clear_button'] ?? self::DEFAULT_SHOW_CLEAR_BUTTON),
            $data['label_clear'] ?? $defaults['label_clear'] ?? self::DEFAULT_LABEL_CLEAR
        );
    }

    public function toArray(): array {
        return [
            'post_types' => $this->postTypes,
            'max_viewed' => $this->maxViewed,
            'max_read' => $this->maxRead,
            'max_suggestions' => $this->maxSuggestions,
            'label_continue' => $this->labelContinue,
            'label_completed' => $this->labelCompleted,
            'label_suggestions' => $this->labelSuggestions,
            'label_empty' => $this->labelEmpty,
            'label_loading' => $this->labelLoading,
            'viewed_enabled' => $this->viewedEnabled,
            'completed_enabled' => $this->completedEnabled,
            'require_consent' => $this->requireConsent,
            'consent_message' => $this->consentMessage,
            'consent_checkbox_label' => $this->consentCheckboxLabel,
            'suggestions_cache_hours' => $this->suggestionsCacheHours,
            'show_excerpt' => $this->showExcerpt,
            'show_thumbnail' => $this->showThumbnail,
            'excerpt_length' => $this->excerptLength,
            'read_threshold' => $this->readThreshold,
            'suggestion_order' => $this->suggestionOrder,
            'include_categories' => $this->includeCategories,
            'exclude_categories' => $this->excludeCategories,
            'show_clear_button' => $this->showClearButton,
            'label_clear' => $this->labelClear,
        ];
    }

    public function postTypes(): array {
        return $this->postTypes;
    }

    public function maxViewed(): int {
        return $this->maxViewed;
    }

    public function maxRead(): int {
        return $this->maxRead;
    }

    public function maxSuggestions(): int {
        return $this->maxSuggestions;
    }

    public function labelContinue(): string {
        return $this->labelContinue;
    }

    public function labelCompleted(): string {
        return $this->labelCompleted;
    }

    public function labelSuggestions(): string {
        return $this->labelSuggestions;
    }

    public function labelEmpty(): string {
        return $this->labelEmpty;
    }

    public function labelLoading(): string {
        return $this->labelLoading;
    }

    public function viewedEnabled(): bool {
        return $this->viewedEnabled;
    }

    public function completedEnabled(): bool {
        return $this->completedEnabled;
    }

    public function requireConsent(): bool {
        return $this->requireConsent;
    }

    public function consentMessage(): string {
        return $this->consentMessage;
    }

    public function consentCheckboxLabel(): string {
        return $this->consentCheckboxLabel;
    }

    public function suggestionsCacheHours(): int {
        return $this->suggestionsCacheHours;
    }

    public function showExcerpt(): bool {
        return $this->showExcerpt;
    }

    public function showThumbnail(): bool {
        return $this->showThumbnail;
    }

    public function excerptLength(): int {
        return $this->excerptLength;
    }

    public function readThreshold(): int {
        return $this->readThreshold;
    }

    public function suggestionOrder(): string {
        return $this->suggestionOrder;
    }

    public function includeCategories(): array {
        return $this->includeCategories;
    }

    public function excludeCategories(): array {
        return $this->excludeCategories;
    }

    public function showClearButton(): bool {
        return $this->showClearButton;
    }

    public function labelClear(): string {
        return $this->labelClear;
    }

    public function toJsConfig(): array {
        return [
            'maxViewed' => $this->maxViewed,
            'maxRead' => $this->maxRead,
            'maxSuggestions' => $this->maxSuggestions,
            'viewedEnabled' => $this->viewedEnabled,
            'completedEnabled' => $this->completedEnabled,
            'requireConsent' => $this->requireConsent,
            'suggestionsCacheHours' => $this->suggestionsCacheHours,
            'showExcerpt' => $this->showExcerpt,
            'showThumbnail' => $this->showThumbnail,
            'readThreshold' => $this->readThreshold,
            'showClearButton' => $this->showClearButton,
            'labels' => [
                'continue' => $this->labelContinue,
                'completed' => $this->labelCompleted,
                'suggestions' => $this->labelSuggestions,
                'empty' => $this->labelEmpty,
                'loading' => $this->labelLoading,
                'consentMessage' => $this->consentMessage,
                'consentCheckboxLabel' => $this->consentCheckboxLabel,
                'clear' => $this->labelClear,
            ],
        ];
    }
}