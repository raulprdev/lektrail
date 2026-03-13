<?php

namespace Completionist\Tests;

use Completionist\TrackingService;
use Completionist\Tests\Mocks\MockSettingsRepository;
use Completionist\Tests\Mocks\MockTrackingRepository;
use Completionist\Tests\Mocks\MockUserProvider;
use PHPUnit\Framework\TestCase;

class TrackingServiceTest extends TestCase {

    private MockUserProvider $users;
    private MockTrackingRepository $tracking;
    private MockSettingsRepository $settings;
    private TrackingService $service;

    protected function setUp(): void {
        $this->users = new MockUserProvider();
        $this->tracking = new MockTrackingRepository();
        $this->settings = new MockSettingsRepository(['track_logged_in_users' => true]);
        $this->service = new TrackingService($this->users, $this->tracking, $this->settings);
    }

    public function testTrackViewedForLoggedInUser(): void {
        $this->users->userId = 42;

        $this->service->trackViewed(123);

        $this->assertEquals('viewed', $this->tracking->history[42][123]);
    }

    public function testTrackReadForLoggedInUser(): void {
        $this->users->userId = 42;

        $this->service->trackRead(123);

        $this->assertEquals('read', $this->tracking->history[42][123]);
    }

    public function testDoesNothingForAnonymousUser(): void {
        $this->users->userId = 0;

        $this->service->trackViewed(123);
        $this->service->trackRead(456);

        $this->assertEmpty($this->tracking->history);
    }

    public function testDoesNothingWhenTrackingDisabled(): void {
        $this->users->userId = 42;
        $settings = new MockSettingsRepository(['track_logged_in_users' => false]);
        $service = new TrackingService($this->users, $this->tracking, $settings);

        $service->trackViewed(123);

        $this->assertEmpty($this->tracking->history);
    }

    public function testShouldTrackServerSideReturnsTrueForLoggedInWithSettingEnabled(): void {
        $this->users->userId = 42;

        $this->assertTrue($this->service->shouldTrackServerSide());
    }

    public function testShouldTrackServerSideReturnsFalseForAnonymous(): void {
        $this->users->userId = 0;

        $this->assertFalse($this->service->shouldTrackServerSide());
    }

    public function testShouldTrackServerSideReturnsFalseWhenDisabled(): void {
        $this->users->userId = 42;
        $settings = new MockSettingsRepository(['track_logged_in_users' => false]);
        $service = new TrackingService($this->users, $this->tracking, $settings);

        $this->assertFalse($service->shouldTrackServerSide());
    }

    public function testGetHistoryReturnsDataForLoggedInUser(): void {
        $this->users->userId = 42;
        $this->tracking->track(42, 100, 'viewed');
        $this->tracking->track(42, 200, 'read');

        $history = $this->service->getHistory();

        $this->assertCount(1, $history['viewed']);
        $this->assertCount(1, $history['read']);
    }

    public function testGetHistoryReturnsEmptyForAnonymous(): void {
        $this->users->userId = 0;

        $history = $this->service->getHistory();

        $this->assertEmpty($history['viewed']);
        $this->assertEmpty($history['read']);
    }
}