<?php

namespace Xi\Filelib\Tests\Command;

use Xi\Filelib\Command\Commander;
use InvalidArgumentException;
use Xi\Filelib\Command\CommandDefinition;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;

class CommanderTest extends \Xi\Filelib\Tests\TestCase
{
    private $commander;

    /**
     * @var Commander
     */
    private $commandFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $queue;

    /**
     * @var CommandDefinition
     */
    private $tussi;

    /**
     * @var CommandDefinition
     */
    private $lussi;

    public function setUp()
    {
        $this->tussi = new CommandDefinition('tussi', 'ManateeTussi', ExecutionStrategy::STRATEGY_ASYNCHRONOUS);
        $this->lussi = new CommandDefinition('lussi', 'ManateeLussi', ExecutionStrategy::STRATEGY_SYNCHRONOUS);

        $this->commander = $this->getMock('Xi\Filelib\Command\CommanderClient');
        $this->commander
            ->expects($this->any())
            ->method('getCommandDefinitions')
            ->will(
                $this->returnValue(
                    array(
                        $this->tussi,
                        $this->lussi
                    )
                )
            );

        $this->queue = $this->getMockedQueue();
        $this->commandFactory = new Commander($this->queue, $this->commander);
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Command\Commander'));
    }

    /**
     * @test
     */
    public function shouldInitializeProperly()
    {
        $this->assertAttributeSame(
            array(
                'tussi' => $this->tussi,
                'lussi' => $this->lussi,
            ),
            'commandDefinitions',
            $this->commandFactory
        );
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function gettingInvalidCommandShouldThrowException()
    {
        $this->commandFactory->getCommandStrategy('lussenhof');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function settingInvalidCommandShouldThrowException()
    {
        $this->commandFactory->setCommandStrategy('lussenhof', Commander::STRATEGY_ASYNCHRONOUS);
    }

    /**
     * @test
     */
    public function settingStrategyShouldWork()
    {
        $this->assertEquals(
            Commander::STRATEGY_ASYNCHRONOUS,
            $this->commandFactory->getCommandStrategy('tussi')
        );

        $this->assertSame(
            $this->commandFactory,
            $this->commandFactory->setCommandStrategy('tussi', Commander::STRATEGY_SYNCHRONOUS)
        );

        $this->assertEquals(
            Commander::STRATEGY_SYNCHRONOUS,
            $this->commandFactory->getCommandStrategy('tussi')
        );
    }

    /**
     * @test
     */
    public function createCommandCreatesCommandObject()
    {
        if (!class_exists('ManateeLussi')) {
            $mockClass = $this->getMockClass(
                'Xi\Filelib\Command\Command',
                array(),
                array(),
                'ManateeLussi'
            );
        }

        if (!class_exists('ManateeTussi')) {
            $mockClass = $this->getMockClass(
                'Xi\Filelib\Command\Command',
                array(),
                array(),
                'ManateeTussi'
            );
        }


        $executable = $this->commandFactory->createCommand(
            'tussi'
        );
        $this->assertInstanceOf('Xi\Filelib\Command\Command', $executable);


        $executable = $this->commandFactory->createCommand(
            'lussi'
        );
        $this->assertInstanceOf('Xi\Filelib\Command\Command', $executable);
    }
}
