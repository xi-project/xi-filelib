<?php

namespace Xi\Filelib\Tests\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\Event\FileEvent;

class FileEventTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Event\FileEvent'));
        $this->assertTrue(is_subclass_of('Xi\Filelib\Event\FileEvent', 'Symfony\Component\EventDispatcher\Event'));
        $this->assertTrue(is_subclass_of('Xi\Filelib\Event\FileEvent', 'Xi\Filelib\Event\IdentifiableEvent'));
    }

    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $file = $this->getMockedFile();

        $event = new FileEvent($file);

        $file2 = $event->getFile();
        $this->assertSame($file, $file2);

        $file3 = $event->getIdentifiable();
        $this->assertSame($file, $file3);
    }
}
