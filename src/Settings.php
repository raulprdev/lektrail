<?php

namespace Completionist;

class Settings {

    public const DEFAULT_POST_TYPES = ['post'];
    public const DEFAULT_MAX_VIEWED = 3;
    public const DEFAULT_MAX_READ = 5;
    public const DEFAULT_MAX_SUGGESTIONS = 5;
    public const DEFAULT_PRIVACY_NOTICE = 'This site tracks your reading progress using local storage.';
    public const DEFAULT_LABEL_CONTINUE = 'Continue reading';
    public const DEFAULT_LABEL_COMPLETED = 'Completed';
    public const DEFAULT_LABEL_SUGGESTIONS = 'Suggested reading';
    public const DEFAULT_LABEL_EMPTY = 'Start reading to track your progress!';
    public const DEFAULT_LABEL_LOADING = 'Loading suggestions...';

    private array $postTypes;
    private int $maxViewed;
    private int $maxRead;
    private int $maxSuggestions;
    private string $privacyNotice;
    private string $labelContinue;
    private string $labelCompleted;
    private string $labelSuggestions;
    private string $labelEmpty;
    private string $labelLoading;

    public function __construct(
        array $postTypes = self::DEFAULT_POST_TYPES,
        int $maxViewed = self::DEFAULT_MAX_VIEWED,
        int $maxRead = self::DEFAULT_MAX_READ,
        int $maxSuggestions = self::DEFAULT_MAX_SUGGESTIONS,
        string $privacyNotice = self::DEFAULT_PRIVACY_NOTICE,
        string $labelContinue = self::DEFAULT_LABEL_CONTINUE,
        string $labelCompleted = self::DEFAULT_LABEL_COMPLETED,
        string $labelSuggestions = self::DEFAULT_LABEL_SUGGESTIONS,
        string $labelEmpty = self::DEFAULT_LABEL_EMPTY,
        string $labelLoading = self::DEFAULT_LABEL_LOADING
    ) {
        $this->postTypes = $postTypes;
        $this->maxViewed = $maxViewed;
        $this->maxRead = $maxRead;
        $this->maxSuggestions = $maxSuggestions;
        $this->privacyNotice = $privacyNotice;
        $this->labelContinue = $labelContinue;
        $this->labelCompleted = $labelCompleted;
        $this->labelSuggestions = $labelSuggestions;
        $this->labelEmpty = $labelEmpty;
        $this->labelLoading = $labelLoading;
    }

    public static function fromArray(array $data, array $defaults = []): self {
        return new self(
            $data['post_types'] ?? $defaults['post_types'] ?? self::DEFAULT_POST_TYPES,
            $data['max_viewed'] ?? $defaults['max_viewed'] ?? self::DEFAULT_MAX_VIEWED,
            $data['max_read'] ?? $defaults['max_read'] ?? self::DEFAULT_MAX_READ,
            $data['max_suggestions'] ?? $defaults['max_suggestions'] ?? self::DEFAULT_MAX_SUGGESTIONS,
            $data['privacy_notice'] ?? $defaults['privacy_notice'] ?? self::DEFAULT_PRIVACY_NOTICE,
            $data['label_continue'] ?? $defaults['label_continue'] ?? self::DEFAULT_LABEL_CONTINUE,
            $data['label_completed'] ?? $defaults['label_completed'] ?? self::DEFAULT_LABEL_COMPLETED,
            $data['label_suggestions'] ?? $defaults['label_suggestions'] ?? self::DEFAULT_LABEL_SUGGESTIONS,
            $data['label_empty'] ?? $defaults['label_empty'] ?? self::DEFAULT_LABEL_EMPTY,
            $data['label_loading'] ?? $defaults['label_loading'] ?? self::DEFAULT_LABEL_LOADING
        );
    }

    public function toArray(): array {
        return [
            'post_types' => $this->postTypes,
            'max_viewed' => $this->maxViewed,
            'max_read' => $this->maxRead,
            'max_suggestions' => $this->maxSuggestions,
            'privacy_notice' => $this->privacyNotice,
            'label_continue' => $this->labelContinue,
            'label_completed' => $this->labelCompleted,
            'label_suggestions' => $this->labelSuggestions,
            'label_empty' => $this->labelEmpty,
            'label_loading' => $this->labelLoading,
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

    public function privacyNotice(): string {
        return $this->privacyNotice;
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
}