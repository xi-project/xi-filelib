<?php

namespace Xi\Filelib\Tests\ExecutionStrategy;

use Xi\Filelib\Command\ExecutionStrategy\AsynchronousExecutionStrategy;

class AsynchronousExecutionStrategyTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function shouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Command\ExecutionStrategy\AsynchronousExecutionStrategy'));
        $this->assertContains(
            'Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy',
            class_implements('Xi\Filelib\Command\ExecutionStrategy\AsynchronousExecutionStrategy')
        );
    }

    /**
     * @test
     */
    public function executes()
    {
        $command = $this
            ->getMockBuilder('Xi\Filelib\File\Command\AfterUploadFileCommand')
            ->disableOriginalConstructor()
            ->getMock();
        $command
            ->expects($this->once())
            ->method('getTopic')
            ->will($this->returnValue('topic'));

        $command
            ->expects($this->never())
            ->method('execute');

        $queue = $this->getMockedQueue();
        $queue
            ->expects($this->once())
            ->method('enqueue')
            ->with('topic', $command)
            ->will($this->returnValue('queue vadis?'));

        $strategy = new ASynchronousExecutionStrategy($queue);
        $ret = $strategy->execute($command);

        $this->assertSame('queue vadis?', $ret);
    }

    /**
     * @test
     */
    public function throwsUpWhenCommandNotSerializable()
    {
        $command = $this
            ->getMockBuilder('Xi\Filelib\File\Command\UploadFileCommand')
            ->disableOriginalConstructor()
            ->getMock();
        $command
            ->expects($this->never())
            ->method('getTopic');

        $command
            ->expects($this->never())
            ->method('execute');

        $queue = $this->getMockedQueue();
        $queue
            ->expects($this->never())
            ->method('enqueue');

        $strategy = new ASynchronousExecutionStrategy($queue);

        $this->setExpectedException('Xi\Filelib\InvalidArgumentException');
        $strategy->execute($command);
    }

}
