<?php

namespace Xi\Tests\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\Event\FileProfileEvent;

class FileProfileEventTest extends \Xi\Tests\Filelib\TestCase
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
        $profile = $this->getMockForAbstractClass('Xi\Filelib\File\FileProfile');
        $event = new FileProfileEvent($profile);

        $profile2 = $event->getProfile();
        $this->assertSame($profile, $profile2);
    }
}
