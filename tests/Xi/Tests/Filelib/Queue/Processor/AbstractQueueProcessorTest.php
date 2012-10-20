<?php

namespace Xi\Tests\Filelib\Queue\Processor;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\Queue\Queue;

use Xi\Filelib\Folder\Command\DeleteFolderCommand;

class AbstractQueueProcessorTest extends \Xi\Tests\Filelib\TestCase
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
        $filelib = new FileLibrary();

        $queue = $this->getMockForAbstractClass('Xi\Filelib\Queue\Queue');
        $fiop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();
        $foop = $this->getMockBuilder('Xi\Filelib\Folder\FolderOperator')->disableOriginalConstructor()->getMock();

        $filelib->setQueue($queue);
        $filelib->setFileOperator($fiop);
        $filelib->setFolderOperator($foop);

        $processor = $this->getMockBuilder('Xi\Filelib\Queue\Processor\AbstractQueueProcessor')
                          ->setMethods(array('process'))
                          ->setConstructorArgs(array($filelib))
                          ->getMockForAbstractClass();


        $this->assertSame($filelib, $processor->getFilelib());
        $this->assertSame($queue, $processor->getQueue());
        $this->assertSame($foop, $processor->getFolderOperator());
        $this->assertSame($fiop, $processor->getFileOperator());

    }

    /**
     * @test
     */
    public function injectOperatorsShouldInjectOperatorsToCommand()
    {
        $command = new TestCommand();

        $this->assertAttributeEquals(null, 'fileOperator', $command);
        $this->assertAttributeEquals(null, 'folderOperator', $command);

        $fiop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();
        $foop = $this->getMockBuilder('Xi\Filelib\Folder\FolderOperator')->disableOriginalConstructor()->getMock();

        $processor = $this->getMockBuilder('Xi\Filelib\Queue\Processor\AbstractQueueProcessor')
                          ->setMethods(array('process', 'getFileOperator', 'getFolderOperator'))
                          ->disableOriginalConstructor()
                          ->getMock();

        $processor->expects($this->atLeastOnce())->method('getFileOperator')
                  ->will($this->returnValue($fiop));

        $processor->expects($this->atLeastOnce())->method('getFolderOperator')
                  ->will($this->returnValue($foop));

        $processor->injectOperators($command);

        $this->assertAttributeSame($foop, 'folderOperator', $command);
        $this->assertAttributeSame($fiop, 'fileOperator', $command);

    }




}

