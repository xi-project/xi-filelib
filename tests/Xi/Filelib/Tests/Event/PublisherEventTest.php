<?php

namespace Xi\Filelib\Tests\Event;

use Xi\Filelib\Event\PublisherEvent;
use Xi\Filelib\File\File;
use Xi\Filelib\Versionable\Version;

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

        $versions = $event->getVersions();
        $this->assertCount(2, $versions);

        $this->assertEquals(
            array(
                Version::get('tussi'),
                Version::get('lussi')
            ),
            $versions
        );
    }
}
