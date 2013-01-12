<?php

namespace Xi\Tests\Filelib\Event;

use Xi\Filelib\Event\FolderEvent;

class FolderEventTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Event\FolderEvent'));
        $this->assertTrue(is_subclass_of('Xi\Filelib\Event\FolderEvent', 'Symfony\Component\EventDispatcher\Event'));
        $this->assertTrue(is_subclass_of('Xi\Filelib\Event\FolderEvent', 'Xi\Filelib\Event\IdentifiableEvent'));
    }

    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $folder = $this->getMock('Xi\Filelib\Folder\Folder');
        $event = new FolderEvent($folder);
        $folder2 = $event->getFolder();
        $this->assertSame($folder, $folder2);

        $folder3 = $event->getIdentifiable();
        $this->assertSame($folder, $folder3);
    }
}
