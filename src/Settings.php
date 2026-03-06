<?php

namespace Completionist;

class Settings {

    public const DEFAULT_POST_TYPES = ['post'];
    public const DEFAULT_MAX_VIEWED = 3;
    public const DEFAULT_MAX_READ = 5;
    public const DEFAULT_MAX_SUGGESTIONS = 5;
    public const DEFAULT_PRIVACY_NOTICE = 'This site tracks your reading progress using local storage.';

    private array $postTypes;
    private int $maxViewed;
    private int $maxRead;
    private int $maxSuggestions;
    private string $privacyNotice;

    public function __construct(
        array $postTypes = self::DEFAULT_POST_TYPES,
        int $maxViewed = self::DEFAULT_MAX_VIEWED,
        int $maxRead = self::DEFAULT_MAX_READ,
        int $maxSuggestions = self::DEFAULT_MAX_SUGGESTIONS,
        string $privacyNotice = self::DEFAULT_PRIVACY_NOTICE
    ) {
        $this->postTypes = $postTypes;
        $this->maxViewed = $maxViewed;
        $this->maxRead = $maxRead;
        $this->maxSuggestions = $maxSuggestions;
        $this->privacyNotice = $privacyNotice;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['post_types'] ?? self::DEFAULT_POST_TYPES,
            $data['max_viewed'] ?? self::DEFAULT_MAX_VIEWED,
            $data['max_read'] ?? self::DEFAULT_MAX_READ,
            $data['max_suggestions'] ?? self::DEFAULT_MAX_SUGGESTIONS,
            $data['privacy_notice'] ?? self::DEFAULT_PRIVACY_NOTICE
        );
    }

    public function toArray(): array {
        return [
            'post_types' => $this->postTypes,
            'max_viewed' => $this->maxViewed,
            'max_read' => $this->maxRead,
            'max_suggestions' => $this->maxSuggestions,
            'privacy_notice' => $this->privacyNotice,
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
}