<?php

namespace Xi\Filelib\Tests\Event;

use Xi\Filelib\Event\VersionProviderEvent;
use Xi\Filelib\File\File;

class VersionProviderEventTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $vp = $this->getMockedVersionProvider();
        $file = File::create();
        $versions = array(
            'tussi',
            'lussi'
        );

        $event = new VersionProviderEvent($vp, $file, $versions);
        $this->assertSame($file, $event->getFile());
        $this->assertSame($vp, $event->getProvider());
        $this->assertEquals($versions, $event->getVersions());
    }
}
