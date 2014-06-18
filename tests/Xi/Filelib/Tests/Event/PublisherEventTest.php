<?php

namespace Xi\Filelib\Tests\Event;

use Xi\Filelib\Event\PublisherEvent;
use Xi\Filelib\File\File;

class PublisherEventTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $file = File::create();
        $versions = array(
            'tussi',
            'lussi'
        );

        $event = new PublisherEvent($file, $versions);
        $this->assertSame($file, $event->getFile());
        $this->assertEquals($versions, $event->getVersions());
    }
}
