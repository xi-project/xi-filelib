<?php

namespace Xi\Filelib\Tests\Queue\Processor;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\Queue\Queue;

class AbstractQueueProcessorTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExists()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Queue\Processor\AbstractQueueProcessor'));
        $this->assertContains('Xi\Filelib\Queue\Processor\QueueProcessor', class_implements('Xi\Filelib\Queue\Processor\AbstractQueueProcessor'));
    }

    /**
     * @test
     */
    public function queueProcessorShouldInitializeProperly()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());

        $queue = $this->getMock('Xi\Filelib\Queue\Queue');

        $filelib->setQueue($queue);

        $processor = $this->getMockBuilder('Xi\Filelib\Queue\Processor\AbstractQueueProcessor')
                          ->setMethods(array('process'))
                          ->setConstructorArgs(array($filelib))
                          ->getMockForAbstractClass();

        $this->assertSame($filelib, $processor->getFilelib());
        $this->assertSame($queue, $processor->getQueue());
    }

}
