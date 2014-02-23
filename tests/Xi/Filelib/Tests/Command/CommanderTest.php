<?php

namespace Xi\Filelib\Tests\Command;

use Xi\Filelib\Command\Commander;
use InvalidArgumentException;
use Xi\Filelib\Command\CommandDefinition;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;

class CommanderTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var Commander
     */
    private $commander;

    private $client;

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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filelib;

    public function setUp()
    {
        $this->tussi = new CommandDefinition('ManateeTussi', ExecutionStrategy::STRATEGY_ASYNCHRONOUS);
        $this->lussi = new CommandDefinition('ManateeLussi');

        $this->client = $this->getMock('Xi\Filelib\Command\CommanderClient');
        $this->client
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

        $this->filelib = $this->getMockedFilelib(null, null, null, null, null, null, null, $this->queue);

        $this->commander = new Commander($this->filelib);
        $this->commander->setQueue($this->queue);

        $this->commander->addClient($this->client);
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Command\Commander');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function gettingInvalidStrategyShouldThrowException()
    {
        $this->commander->getExecutionStrategy('lussenhof');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function settingInvalidStrategyShouldThrowException()
    {
        $this->commander->setExecutionStrategy('lussenhof', ExecutionStrategy::STRATEGY_ASYNCHRONOUS);
    }

    /**
     * @test
     */
    public function settingStrategyShouldWork()
    {
        $this->assertEquals(
            ExecutionStrategy::STRATEGY_ASYNCHRONOUS,
            $this->commander->getExecutionStrategy('ManateeTussi')
        );

        $this->assertSame(
            $this->commander,
            $this->commander->setExecutionStrategy('ManateeTussi', ExecutionStrategy::STRATEGY_SYNCHRONOUS)
        );

        $this->assertEquals(
            ExecutionStrategy::STRATEGY_SYNCHRONOUS,
            $this->commander->getExecutionStrategy('ManateeTussi')
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


        $executable = $this->commander->createCommand(
            'ManateeTussi'
        );
        $this->assertInstanceOf('Xi\Filelib\Command\Command', $executable);


        $executable = $this->commander->createCommand(
            'ManateeLussi'
        );
        $this->assertInstanceOf('Xi\Filelib\Command\Command', $executable);
    }
}
