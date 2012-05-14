<?php

namespace Xi\Tests\Filelib\Queue\Processor;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Queue\Processor\DefaultQueueProcessor;
use Xi\Filelib\Queue\Message;
use Xi\Tests\Filelib\Queue\Processor\TestCommand;

class DefaultQueueProcessorTest extends \Xi\Tests\Filelib\TestCase
{

    protected $queue;
    
    protected $fileOperator;
    
    protected $folderOperator;
    
    protected $filelib;
    
    public function setUp()
    {
        $filelib = new FileLibrary();
         
        $queue = $this->getMockForAbstractClass('Xi\Filelib\Queue\Queue');
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $foop = $this->getMockForAbstractClass('Xi\Filelib\Folder\FolderOperator');
        
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
    public function processShouldExitEarlyIfQueueIsEmpty()
    {
        $processor = new DefaultQueueProcessor($this->filelib);
        
        $this->queue->expects($this->once())->method('dequeue')->will($this->returnValue(null));
        $ret = $processor->process();
        $this->assertNull($ret);
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
    public function processShouldTryToProcessWhenACommandIsDequeued()
    {
        $processor = $this->getMockBuilder('Xi\Filelib\Queue\Processor\DefaultQueueProcessor')
                          ->setConstructorArgs(array($this->filelib))
                          ->setMethods(array('tryToProcess'))
                          ->getMock();

        // $processor = new DefaultQueueProcessor($this->filelib);
        
        $command = new TestCommand();
        
        $message = new Message(serialize($command));
        
        $this->queue->expects($this->once())->method('dequeue')->will($this->returnValue($message));
        
        $processor->expects($this->once())->method('tryToProcess');
        
        $processor->process();
        
    }
    
    /**
     * @test
     */
    public function tryToProcessShouldReturnFalseWhenProcessorFunctionThrowsException()
    {
        $processor = new DefaultQueueProcessor($this->filelib);
        
        $processorFunc = function() {
            throw new \Xi\Filelib\FilelibException('Lussen lussen luu!');
        };
        
        $message = new Message(serialize('Tussihofen'));
        
        $ret = $processor->tryToProcess($message, $processorFunc);
        
        $this->assertFalse($ret);
                
    }
    
    /**
     * @test
     */
    public function tryToProcessShouldAckMessageWhenProcessorFunctionDoesntThrowException()
    {
        $processor = new DefaultQueueProcessor($this->filelib);
        
        $processorFunc = function() {
            return true;
        };
        
        $message = new Message(serialize('Tussihofen'));
        
        $this->queue->expects($this->once())->method('ack')->with($this->equalTo($message));
                
        $ret = $processor->tryToProcess($message, $processorFunc);
        
        $this->assertTrue($ret);
    }

    
    /**
     * @test
     */
    public function tryToProcessShouldEnqueeWhenProcessorFunctionReturnsANewCommand()
    {
        $processor = new DefaultQueueProcessor($this->filelib);
        
        $processorFunc = function() {
            return new TestCommand();
        };
        
        $message = new Message(serialize('Tussihofen'));
        
        $this->queue->expects($this->once())->method('ack')->with($this->equalTo($message));
        
        $this->queue->expects($this->once())->method('enqueue')->with($this->isInstanceOf('Xi\Filelib\Queue\Message'));
        
        $ret = $processor->tryToProcess($message, $processorFunc);
        
        $this->assertTrue($ret);
    }

    
    
    
    /**
     * @test
     */
    public function tryToProcessShouldExecuteCommand()
    {
        $processor = $this->getMockBuilder('Xi\Filelib\Queue\Processor\DefaultQueueProcessor')
                          ->setConstructorArgs(array($this->filelib))
                          ->setMethods(array('extractCommandFromMessage'))
                          ->getMock();
        
        $command = $this->getMock('Xi\Tests\Filelib\Queue\Processor\TestCommand');
        
        $message = new Message(serialize($command));
        
        $this->queue->expects($this->once())->method('dequeue')->will($this->returnValue($message));
        
        
        $processor->expects($this->once())->method('extractCommandFromMessage')
                  ->with($this->isInstanceOf('Xi\Filelib\Queue\Message'))
                  ->will($this->returnValue($command));
                
        $command->expects($this->once())->method('execute');
                
        $processor->process();
    }
    
    
    

}

