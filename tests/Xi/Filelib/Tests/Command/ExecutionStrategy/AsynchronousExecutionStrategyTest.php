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
    public function executeShouldExecuteCommand()
    {
        $command = $this->getMockedCommand('topic');
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
}
