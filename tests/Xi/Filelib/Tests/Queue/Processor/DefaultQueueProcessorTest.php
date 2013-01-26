<?php

namespace Xi\Filelib\Tests\Queue\Processor;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Queue\Processor\DefaultQueueProcessor;
use Xi\Filelib\Queue\Message;
use Xi\Filelib\Tests\Queue\Processor\TestCommand;

class DefaultQueueProcessorTest extends \Xi\Filelib\Tests\TestCase
{
    protected $queue;

    protected $fileOperator;

    protected $folderOperator;

    protected $filelib;

    public function setUp()
    {
        $filelib = new FileLibrary();

        $queue = $this->getMockForAbstractClass('Xi\Filelib\Queue\Queue');
        $fiop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();
        $foop = $this->getMockBuilder('Xi\Filelib\Folder\FolderOperator')->disableOriginalConstructor()->getMock();

        $filelib->setQueue($queue);
        $filelib->setFileOperator($fiop);
        $filelib->setFolderOperator($foop);

        $this->queue = $queue;
        $this->fileOperator = $fiop;
        $this->folderOperator = $foop;
        $this->filelib = $filelib;
    }

    /**
     * @test
     */
    public function classShouldExists()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Queue\Processor\DefaultQueueProcessor'));
        $this->assertContains('Xi\Filelib\Queue\Processor\QueueProcessor', class_implements('Xi\Filelib\Queue\Processor\DefaultQueueProcessor'));
    }

    /**
     * @test
     */
    public function processReturnsFalseIfQueueIsEmpty()
    {
        $processor = new DefaultQueueProcessor($this->filelib);

        $this->queue->expects($this->once())->method('dequeue')->will($this->returnValue(null));

        $this->assertFalse($processor->process());
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function processShouldThrowExceptionWhenSomethingOtherThanACommandIsDequeued()
    {
        $processor = new DefaultQueueProcessor($this->filelib);

        $message = new Message(serialize('A total eclipse of the heart'));
        $this->queue->expects($this->once())->method('dequeue')->will($this->returnValue($message));

        $processor->process();
    }

    /**
     * @test
     */
    public function processShouldProcessMessageWhenACommandIsDequeued()
    {
        $processor = $this->getMockBuilder('Xi\Filelib\Queue\Processor\DefaultQueueProcessor')
                          ->setConstructorArgs(array($this->filelib))
                          ->setMethods(array('processMessage'))
                          ->getMock();

        $command = new TestCommand();

        $message = new Message(serialize($command));

        $this->queue->expects($this->once())->method('dequeue')->will($this->returnValue($message));

        $processor->expects($this->once())->method('processMessage')->with($message);

        $processor->process();
    }

     /**
      * @test
      */
    public function processMessageDoesNotCatchExceptionFromProcessorFunction()
    {
        $processor = new DefaultQueueProcessor($this->filelib);

        $processorFunc = function() {
            throw new \Xi\Filelib\FilelibException('Lussen lussen luu!');
        };

        $message = new Message(serialize('Tussihofen'));

        $this->setExpectedException(
            'Xi\Filelib\FilelibException',
            'Lussen lussen luu!'
        );

        $processor->processMessage($message, $processorFunc);
    }

    /**
     * @test
     */
    public function processMessageShouldAckMessageWhenProcessorFunctionDoesntThrowException()
    {
        $processor = new DefaultQueueProcessor($this->filelib);

        $processorFunc = function() {
            return true;
        };

        $message = new Message(serialize('Tussihofen'));

        $this->queue->expects($this->once())->method('ack')->with($this->equalTo($message));

        $processor->processMessage($message, $processorFunc);
    }

    /**
     * @test
     */
    public function processMessageShouldEnqueeWhenProcessorFunctionReturnsANewCommand()
    {
        $processor = new DefaultQueueProcessor($this->filelib);

        $processorFunc = function() {
            return new TestCommand();
        };

        $message = new Message(serialize('Tussihofen'));

        $this->queue->expects($this->once())->method('ack')->with($this->equalTo($message));

        $this->queue->expects($this->once())->method('enqueue')->with($this->isInstanceOf('Xi\Filelib\Command'));

        $processor->processMessage($message, $processorFunc);
    }

    /**
     * @test
     */
    public function processMessageShouldExecuteCommand()
    {
        $processor = $this->getMockBuilder('Xi\Filelib\Queue\Processor\DefaultQueueProcessor')
                          ->setConstructorArgs(array($this->filelib))
                          ->setMethods(array('extractCommandFromMessage'))
                          ->getMock();

        $command = $this->getMock('Xi\Filelib\Tests\Queue\Processor\TestCommand');

        $message = new Message(serialize($command));

        $this->queue->expects($this->once())->method('dequeue')->will($this->returnValue($message));


        $processor->expects($this->once())->method('extractCommandFromMessage')
                  ->with($this->isInstanceOf('Xi\Filelib\Queue\Message'))
                  ->will($this->returnValue($command));

        $command->expects($this->once())->method('execute');

        $processor->process();
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function extractCommandFromMessageShouldThrowExceptionIfCommandIsNotFound()
    {
        $lus = serialize('tussi');
        $message = new Message($lus);

        $processor = new DefaultQueueProcessor($this->filelib);

        $processor->extractCommandFromMessage($message);
    }

    /**
     * @test
     */
    public function extractCommandFromMessageShouldReturnCommand()
    {
        $lus = serialize(new TestCommand());
        $message = new Message($lus);

        $processor = new DefaultQueueProcessor($this->filelib);

        $ret = $processor->extractCommandFromMessage($message);

        $this->assertInstanceOf('Xi\Filelib\Tests\Queue\Processor\TestCommand', $ret);
    }
}
