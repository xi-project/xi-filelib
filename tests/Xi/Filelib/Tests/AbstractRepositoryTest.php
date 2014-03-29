<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\AbstractRepository;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;
use Xi\Filelib\FileLibrary;

class AbstractRepositoryTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $commander;

    /**
     * @var AbstractRepository
     */
    private $operator;

    public function setUp()
    {
        $this->operator = $this->getMockBuilder('Xi\Filelib\AbstractRepository')
            ->setMethods(array())
            ->getMockForAbstractClass();

        $this->commander = $this->getMockedCommander();

        $filelib = new FileLibrary(
            $this->getMockedStorage(),
            $this->getMockedPlatform(),
            $this->getMockedEventDispatcher(),
            $this->commander
        );

        $this->operator->attachTo($filelib);
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\AbstractRepository');
        $this->assertImplements('Xi\Filelib\Attacher', 'Xi\Filelib\AbstractRepository');
        $this->assertImplements('Xi\Filelib\Command\CommanderClient', 'Xi\Filelib\AbstractRepository');
    }

    /**
     * @test
     */
    public function getExecutionStrategyDelegates()
    {
        $this->commander
            ->expects($this->once())
            ->method('getExecutionStrategy')
            ->with('lusso')
            ->will($this->returnValue(ExecutionStrategy::STRATEGY_ASYNCHRONOUS));

        $this->assertSame(
            ExecutionStrategy::STRATEGY_ASYNCHRONOUS,
            $this->operator->getExecutionStrategy('lusso')
        );
    }

    /**
     * @test
     */
    public function setExecutionStrategyDelegates()
    {
        $this->commander
            ->expects($this->once())
            ->method('setExecutionStrategy')
            ->with('lusso', ExecutionStrategy::STRATEGY_ASYNCHRONOUS)
            ->will($this->returnValue(null));

        $this->assertSame(
            $this->operator,
            $this->operator->setExecutionStrategy('lusso', ExecutionStrategy::STRATEGY_ASYNCHRONOUS)
        );
    }

    /**
     * @test
     */
    public function createExecutableDelegates()
    {
        $executable = $this->getMockedExecutable();

        $this->commander
            ->expects($this->once())
            ->method('createExecutable')
            ->with('lusso', array('tussi' => 'lussi'))
            ->will($this->returnValue($executable));

        $this->assertSame(
            $executable,
            $this->operator->createExecutable('lusso', array('tussi' => 'lussi'))
        );
    }

    /**
     * @test
     */
    public function createCommandDelegates()
    {
        $command = $this->getMockedCommand();

        $this->commander
            ->expects($this->once())
            ->method('createCommand')
            ->with('lusso', array('tussi' => 'lussi'))
            ->will($this->returnValue($command));

        $this->assertSame(
            $command,
            $this->operator->createCommand('lusso', array('tussi' => 'lussi'))
        );
    }

}
