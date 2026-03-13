<?php

namespace Completionist;

use Completionist\Contracts\Context;
use Completionist\Contracts\Hooks;
use Completionist\Contracts\SettingsRepository;

class Plugin
{
    private Hooks $hooks;
    private Assets $assets;
    private SettingsRepository $settings;
    private Context $context;
    private SuggestionsEndpoint $suggestions;
    private Shortcode $shortcode;
    private AdminPage $adminPage;
    private TrackingService $trackingService;
    private TrackingEndpoint $trackingEndpoint;

    public function __construct(
        Assets $assets,
        SettingsRepository $settings,
        Context $context,
        SuggestionsEndpoint $suggestions,
        Shortcode $shortcode,
        AdminPage $adminPage,
        Hooks $hooks,
        TrackingService $trackingService,
        TrackingEndpoint $trackingEndpoint
    ) {
        $this->assets = $assets;
        $this->settings = $settings;
        $this->context = $context;
        $this->suggestions = $suggestions;
        $this->shortcode = $shortcode;
        $this->adminPage = $adminPage;
        $this->hooks = $hooks;
        $this->trackingService = $trackingService;
        $this->trackingEndpoint = $trackingEndpoint;
    }

    public function run(): void
    {
        $this->suggestions->register($this->hooks);
        $this->shortcode->register($this->hooks);
        $this->adminPage->register($this->hooks);
        $this->trackingEndpoint->register($this->hooks);
        $this->hooks->addAction('wp_enqueue_scripts', [$this, 'enqueueDetector']);
    }

    public function enqueueDetector(): void
    {
        if (!$this->shouldEnqueueDetector()) {
            return;
        }
        $postId = $this->context->getPostId();
        $serverSideTracking = $this->trackingService->shouldTrackServerSide();
        $this->trackingService->trackViewed($postId);
        $this->assets->enqueueDetector($postId, $serverSideTracking);
    }

    private function shouldEnqueueDetector(): bool
    {
        $postTypes = $this->settings->load()->postTypes();
        return $this->context->isSingular($postTypes);
    }
}
