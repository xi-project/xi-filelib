<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Command\Executable;

class ExecutableTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Command\Executable'));
    }

    /**
     * @test
     */
    public function shouldDelegateExecutionToStrategy()
    {
        $command = $this->getMockedCommand();
        $strategy = $this->getMockedExecutionStrategy();

        $executable = new Executable($command, $strategy);

        $strategy
            ->expects($this->once())
            ->method('execute')
            ->with($command)
            ->will($this->returnValue('tussi'));

        $ret = $executable->execute();

        $this->assertSame('tussi', $ret);
    }
}
