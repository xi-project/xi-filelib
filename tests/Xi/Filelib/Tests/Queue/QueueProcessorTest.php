<?php

namespace Xi\Filelib\Tests\Queue\Processor;

use Xi\Filelib\Queue\QueueProcessor;
use Xi\Filelib\Queue\Message;
use Xi\Filelib\Tests\Queue\TestCommand;

class QueueProcessorTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $queue;

    protected $fileOperator;

    protected $folderOperator;

    protected $filelib;

    public function setUp()
    {
        $fiop = $this->getMockedFileOperator();
        $foop = $this->getMockedFolderOperator();
        $filelib = $this->getMockedFilelib(array(), $fiop, $foop);

        $queue = $this->getMock('Xi\Filelib\Queue\Queue');
        $filelib->setQueue($queue);

        $this->queue = $queue;
        $this->fileOperator = $fiop;
        $this->folderOperator = $foop;
        $this->filelib = $filelib;
    }

    /**
     * @test
     */
    public function getFilelibShouldReturnFilelib()
    {
        $processor = new QueueProcessor($this->filelib);
        $this->assertSame($this->filelib, $processor->getFilelib());
    }


    /**
     * @test
     */
    public function classShouldExists()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Queue\QueueProcessor'));
    }

    /**
     * @test
     */
    public function processReturnsFalseIfQueueIsEmpty()
    {
        $processor = new QueueProcessor($this->filelib);

        $this->queue->expects($this->once())->method('dequeue')->will($this->returnValue(null));

        $this->assertFalse($processor->process());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function processShouldThrowExceptionWhenSomethingOtherThanACommandIsDequeued()
    {
        $processor = new QueueProcessor($this->filelib);

        $message = new Message(serialize('A total eclipse of the heart'));
        $this->queue->expects($this->once())->method('dequeue')->will($this->returnValue($message));

        $processor->process();
    }

    /**
     * @test
     */
    public function processShouldProcessMessageWhenACommandIsDequeued()
    {
        $processor = $this->getMockBuilder('Xi\Filelib\Queue\QueueProcessor')
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
        $processor = new QueueProcessor($this->filelib);

        $processorFunc = function() {
            throw new \Xi\Filelib\InvalidArgumentException('Lussen lussen luu!');
        };

        $message = new Message(serialize('Tussihofen'));

        $this->setExpectedException(
            'Xi\Filelib\InvalidArgumentException',
            'Lussen lussen luu!'
        );

        $processor->processMessage($message, $processorFunc);
    }

    /**
     * @test
     */
    public function processMessageShouldAckMessageWhenProcessorFunctionDoesntThrowException()
    {
        $processor = new QueueProcessor($this->filelib);

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
        $processor = new QueueProcessor($this->filelib);

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
        $processor = $this->getMockBuilder('Xi\Filelib\Queue\QueueProcessor')
                          ->setConstructorArgs(array($this->filelib))
                          ->setMethods(array('extractCommandFromMessage'))
                          ->getMock();

        $command = $this->getMock('Xi\Filelib\Tests\Queue\TestCommand');

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

        $processor = new QueueProcessor($this->filelib);

        $processor->extractCommandFromMessage($message);
    }

    /**
     * @test
     */
    public function extractCommandFromMessageShouldReturnCommand()
    {
        $lus = serialize(new TestCommand());
        $message = new Message($lus);

        $processor = new QueueProcessor($this->filelib);

        $ret = $processor->extractCommandFromMessage($message);

        $this->assertInstanceOf('Xi\Filelib\Tests\Queue\TestCommand', $ret);
    }
}
