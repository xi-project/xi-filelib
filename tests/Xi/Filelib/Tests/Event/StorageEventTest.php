<?php

namespace Xi\Filelib\Tests\Event;

use Xi\Filelib\Event\StorageEvent;

class StorageEventTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Event\StorageEvent');
    }

    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $event = new StorageEvent($path);
        $this->assertEquals($path, $event->getPath());
    }

    /**
     * @test
     */
    public function pathCanBeChanged()
    {
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $event = new StorageEvent($path);
        $this->assertEquals($path, $event->getPath());

        $this->assertSame($event, $event->setPath('lussutiluu'));
        $this->assertEquals('lussutiluu', $event->getPath());
    }
}
