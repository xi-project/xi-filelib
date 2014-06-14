<?php

namespace Xi\Filelib\Tests\Event;

use Xi\Filelib\Event\FileProfileEvent;

class FileProfileEventTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Event\FileProfileEvent'));
        $this->assertContains(
            'Symfony\Component\EventDispatcher\Event',
            class_parents('Xi\Filelib\Event\FileProfileEvent')
        );
    }

    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $profile = $this->getMockedFileProfile();
        $event = new FileProfileEvent($profile);

        $profile2 = $event->getProfile();
        $this->assertSame($profile, $profile2);
    }
}
