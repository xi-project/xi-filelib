<?php

namespace Xi\Filelib\Tests\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\Event\ResourceEvent;

class ResourceEventTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Event\ResourceEvent'));
        $this->assertTrue(is_subclass_of('Xi\Filelib\Event\ResourceEvent', 'Symfony\Component\EventDispatcher\Event'));
        $this->assertTrue(is_subclass_of('Xi\Filelib\Event\ResourceEvent', 'Xi\Filelib\Event\IdentifiableEvent'));
    }

    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $resource = $this->getMockedResource();
        $event = new ResourceEvent($resource);
        $resource2 = $event->getResource();
        $this->assertSame($resource, $resource2);

        $resource3 = $event->getIdentifiable();
        $this->assertSame($resource, $resource3);
    }
}
