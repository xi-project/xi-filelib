<?php

namespace Xi\Filelib\Tests\Command\ExecutionStrategy;

use Xi\Filelib\Command\ExecutionStrategy\SynchronousExecutionStrategy;

class SynchronousExecutionStrategyTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function shouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Command\ExecutionStrategy\SynchronousExecutionStrategy'));
        $this->assertContains(
            'Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy',
            class_implements('Xi\Filelib\Command\ExecutionStrategy\SynchronousExecutionStrategy')
        );
    }

    /**
     * @test
     */
    public function executeShouldExecuteCommand()
    {
        $command = $this->getMockedCommand('topic');
        $command
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue('executore'));

        $strategy = new SynchronousExecutionStrategy();
        $ret = $strategy->execute($command);

        $this->assertSame('executore', $ret);
    }
}
