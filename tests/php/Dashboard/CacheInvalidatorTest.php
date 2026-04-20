<?php

namespace LekTrail\Tests\Dashboard;

use LekTrail\Dashboard\CacheInvalidator;
use LekTrail\Tests\Mocks\MockHooks;
use LekTrail\Tests\Mocks\MockPostCountRepository;
use PHPUnit\Framework\TestCase;

class CacheInvalidatorTest extends TestCase
{
    public function testRegistersTransitionPostStatusHook(): void
    {
        $hooks = new MockHooks();
        $counts = new MockPostCountRepository();
        $invalidator = new CacheInvalidator($counts);

        $invalidator->register($hooks);

        $this->assertArrayHasKey('transition_post_status', $hooks->actions);
        $this->assertEquals(2, $hooks->actions['transition_post_status']['accepted_args']);
    }

    public function testClearsCacheOnPublish(): void
    {
        $counts = new MockPostCountRepository();
        $invalidator = new CacheInvalidator($counts);

        $invalidator->onTransition('publish', 'draft');

        $this->assertEquals(1, $counts->clearCacheCalls);
    }

    public function testClearsCacheOnUnpublish(): void
    {
        $counts = new MockPostCountRepository();
        $invalidator = new CacheInvalidator($counts);

        $invalidator->onTransition('draft', 'publish');

        $this->assertEquals(1, $counts->clearCacheCalls);
    }

    public function testIgnoresIrrelevantTransitions(): void
    {
        $counts = new MockPostCountRepository();
        $invalidator = new CacheInvalidator($counts);

        $invalidator->onTransition('draft', 'pending');

        $this->assertEquals(0, $counts->clearCacheCalls);
    }
}
