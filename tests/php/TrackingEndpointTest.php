<?php

namespace Completionist\Tests;

use Completionist\Tests\Mocks\MockJsonResponse;
use Completionist\Tests\Mocks\MockPluginConfigRepository;
use Completionist\Tests\Mocks\MockTrackingRepository;
use Completionist\Tests\Mocks\MockUserProvider;
use Completionist\TrackingEndpoint;
use Completionist\TrackingService;
use PHPUnit\Framework\TestCase;

class TrackingEndpointTest extends TestCase
{
    private MockUserProvider $users;
    private MockTrackingRepository $trackings;
    private MockJsonResponse $response;
    private TrackingService $service;
    private TrackingEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->users     = new MockUserProvider();
        $this->trackings = new MockTrackingRepository();
        $this->response  = new MockJsonResponse();
        $pluginConfigs        = new MockPluginConfigRepository([ 'track_logged_in_users' => true]);
        $this->service   = new TrackingService($this->users, $this->trackings, $pluginConfigs);
        $this->endpoint  = new TrackingEndpoint($this->service, $this->response);
        $_POST = [];
    }

    public function testMarksPostAsRead(): void
    {
        $this->users->userId = 42;
        $_POST['post_id'] = '123';

        $this->endpoint->handle();

        $this->assertEquals('read', $this->trackings->history[42][123]);
        $this->assertTrue($this->response->successData['tracked']);
    }

    public function testRequiresPostId(): void
    {
        $this->users->userId = 42;

        $this->endpoint->handle();

        $this->assertEquals('Missing post_id', $this->response->errorMessage);
    }

    public function testRequiresLoggedInUser(): void
    {
        $this->users->userId = 0;
        $_POST['post_id'] = '123';

        $this->endpoint->handle();

        $this->assertEmpty($this->trackings->history);
        $this->assertEquals('Not logged in', $this->response->errorMessage);
    }
}
