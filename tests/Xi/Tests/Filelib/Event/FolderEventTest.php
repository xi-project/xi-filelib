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
        $this->assertContains(
            'Symfony\Component\EventDispatcher\Event',
            class_parents('Xi\Filelib\Event\FolderEvent')
        );
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
    }
}
