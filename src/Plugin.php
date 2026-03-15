<?php

namespace Completionist;

use Completionist\Contracts\Context;
use Completionist\Contracts\Hooks;
use Completionist\Contracts\PluginConfigRepository;
use Completionist\Shortcodes\WidgetShortcode;

class Plugin
{
    private Hooks $hooks;
    private Assets $assets;
    private PluginConfigRepository $pluginConfigs;
    private Context $context;
    private SuggestionsEndpoint $suggestions;
    private PreviewEndpoint $preview;
    private WidgetShortcode $widgetShortcode;
    private AdminPage $adminPage;
    private TrackingService $trackingService;
    private TrackingEndpoint $trackingEndpoint;

    public function __construct(
        Assets $assets,
        PluginConfigRepository $pluginConfigs,
        Context $context,
        SuggestionsEndpoint $suggestions,
        PreviewEndpoint $preview,
        WidgetShortcode $widgetShortcode,
        AdminPage $adminPage,
        Hooks $hooks,
        TrackingService $trackingService,
        TrackingEndpoint $trackingEndpoint
    ) {
        $this->assets        = $assets;
        $this->pluginConfigs = $pluginConfigs;
        $this->context       = $context;
        $this->suggestions = $suggestions;
        $this->preview = $preview;
        $this->widgetShortcode = $widgetShortcode;
        $this->adminPage = $adminPage;
        $this->hooks = $hooks;
        $this->trackingService = $trackingService;
        $this->trackingEndpoint = $trackingEndpoint;
    }

    public function run(): void
    {
        $this->suggestions->register($this->hooks);
        $this->preview->register($this->hooks);
        $this->widgetShortcode->register($this->hooks);
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
        $postTypes = $this->pluginConfigs->load()->postTypes();
        return $this->context->isSingular($postTypes);
    }
}
