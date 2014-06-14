<?php

namespace Xi\Filelib\Tests\Event;

use Xi\Filelib\Event\FilelibEvent;

class FilelibEventTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Event\FilelibEvent'));
        $this->assertContains(
            'Symfony\Component\EventDispatcher\Event',
            class_parents('Xi\Filelib\Event\FilelibEvent')
        );
    }

    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $filelib = $this->getMockedFilelib();
        $event = new FilelibEvent($filelib);

        $filelib2 = $event->getFilelib();
        $this->assertSame($filelib, $filelib2);
    }
}
