<?php

namespace Xi\Tests\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\Event\FileCopyEvent;

class FileCopyEventTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Event\FileCopyEvent'));
        $this->assertContains('Symfony\Component\EventDispatcher\Event', class_parents('Xi\Filelib\Event\FileCopyEvent'));
    }

    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $source = $this->getMock('Xi\Filelib\File\File');
        $target = $this->getMock('Xi\Filelib\File\File');

        $event = new FileCopyEvent($source, $target);

        $source2 = $event->getSource();
        $this->assertSame($source, $source2);

        $target2 = $event->getTarget();
        $this->assertSame($target, $target2);
    }

}
