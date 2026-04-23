<?php

namespace LekTrail\Tests\Dashboard;

use LekTrail\Dashboard\DisplayMode;
use PHPUnit\Framework\TestCase;

class DisplayModeTest extends TestCase
{
    public function testDefaultIsProgress(): void
    {
        $mode = new DisplayMode();
        $this->assertTrue($mode->isProgress());
    }

    public function testProgressMode(): void
    {
        $mode = new DisplayMode('progress');
        $this->assertTrue($mode->isProgress());
        $this->assertFalse($mode->isRemaining());
        $this->assertFalse($mode->isCount());
    }

    public function testRemainingMode(): void
    {
        $mode = new DisplayMode('remaining');
        $this->assertTrue($mode->isRemaining());
        $this->assertFalse($mode->isProgress());
    }

    public function testCountMode(): void
    {
        $mode = new DisplayMode('count');
        $this->assertTrue($mode->isCount());
        $this->assertFalse($mode->isProgress());
    }

    public function testInvalidModeDefaultsToProgress(): void
    {
        $mode = new DisplayMode('invalid');
        $this->assertTrue($mode->isProgress());
    }

    public function testFromArrayWithMode(): void
    {
        $mode = DisplayMode::fromArray(['mode' => 'remaining']);
        $this->assertTrue($mode->isRemaining());
    }

    public function testFromArrayWithoutMode(): void
    {
        $mode = DisplayMode::fromArray([]);
        $this->assertTrue($mode->isProgress());
    }
}
