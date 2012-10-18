<?php

namespace Xi\Tests\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\Event\ResourceEvent;

class ResourceEventTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Event\ResourceEvent'));
        $this->assertContains('Symfony\Component\EventDispatcher\Event', class_parents('Xi\Filelib\Event\ResourceEvent'));
    }
    
    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $resource = $this->getMock('Xi\Filelib\File\Resource');
        $event = new ResourceEvent($resource);
        $resource2 = $event->getResource();
        $this->assertSame($resource, $resource2);
    }

}
