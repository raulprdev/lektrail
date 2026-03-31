<?php

namespace LekTrail\Tests;

use LekTrail\Tests\Mocks\MockPluginConfigRepository;
use LekTrail\Tests\Mocks\MockTrackingRepository;
use LekTrail\Tests\Mocks\MockUserProvider;
use LekTrail\TrackingService;
use PHPUnit\Framework\TestCase;

class TrackingServiceTest extends TestCase
{
    private MockUserProvider $users;
    private MockTrackingRepository $trackings;
    private MockPluginConfigRepository $pluginConfigs;
    private TrackingService $service;

    protected function setUp(): void
    {
        $this->users     = new MockUserProvider();
        $this->trackings = new MockTrackingRepository();
        $this->pluginConfigs  = new MockPluginConfigRepository([ 'track_logged_in_users' => true]);
        $this->service   = new TrackingService($this->users, $this->trackings, $this->pluginConfigs);
    }

    public function testTrackViewedForLoggedInUser(): void
    {
        $this->users->userId = 42;

        $this->service->trackViewed(123);

        $this->assertEquals('viewed', $this->trackings->history[42][123]);
    }

    public function testTrackReadForLoggedInUser(): void
    {
        $this->users->userId = 42;

        $this->service->trackRead(123);

        $this->assertEquals('read', $this->trackings->history[42][123]);
    }

    public function testDoesNothingForAnonymousUser(): void
    {
        $this->users->userId = 0;

        $this->service->trackViewed(123);
        $this->service->trackRead(456);

        $this->assertEmpty($this->trackings->history);
    }

    public function testDoesNothingWhenTrackingDisabled(): void
    {
        $this->users->userId = 42;
        $pluginConfigs = new MockPluginConfigRepository([ 'track_logged_in_users' => false]);
        $service = new TrackingService($this->users, $this->trackings, $pluginConfigs);

        $service->trackViewed(123);

        $this->assertEmpty($this->trackings->history);
    }

    public function testShouldTrackServerSideReturnsTrueForLoggedInWithSettingEnabled(): void
    {
        $this->users->userId = 42;

        $this->assertTrue($this->service->shouldTrackServerSide());
    }

    public function testShouldTrackServerSideReturnsFalseForAnonymous(): void
    {
        $this->users->userId = 0;

        $this->assertFalse($this->service->shouldTrackServerSide());
    }

    public function testShouldTrackServerSideReturnsFalseWhenDisabled(): void
    {
        $this->users->userId = 42;
        $pluginConfigs = new MockPluginConfigRepository([ 'track_logged_in_users' => false]);
        $service = new TrackingService($this->users, $this->trackings, $pluginConfigs);

        $this->assertFalse($service->shouldTrackServerSide());
    }

    public function testGetHistoryReturnsDataForLoggedInUser(): void
    {
        $this->users->userId = 42;
        $this->trackings->track(42, 100, 'viewed');
        $this->trackings->track(42, 200, 'read');

        $history = $this->service->getHistory();

        $this->assertCount(1, $history['viewed']);
        $this->assertCount(1, $history['read']);
    }

    public function testGetHistoryReturnsEmptyForAnonymous(): void
    {
        $this->users->userId = 0;

        $history = $this->service->getHistory();

        $this->assertEmpty($history['viewed']);
        $this->assertEmpty($history['read']);
    }
}
