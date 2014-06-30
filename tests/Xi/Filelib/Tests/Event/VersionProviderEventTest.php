<?php

namespace Xi\Filelib\Tests\Event;

use Xi\Filelib\Event\VersionProviderEvent;
use Xi\Filelib\File\File;
use Xi\Filelib\Version;

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
        $this->assertSame($file, $event->getVersionable());
        $this->assertSame($vp, $event->getProvider());
        $this->assertEquals(
            array(
                Version::get('tussi'),
                Version::get('lussi')
            ),
            $event->getVersions()
        );
    }
}
