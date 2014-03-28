<?php

namespace Xi\Filelib\Tests\Command;

use Xi\Filelib\Command\CommandDefinition;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;

class CommandDefinitionTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Command\CommandDefinition');
    }

    /**
     * @test
     */
    public function shouldInitializeCorrectly()
    {
        $definition = new CommandDefinition(
            'Lussen\Sie\Tussen',
            ExecutionStrategy::STRATEGY_SYNCHRONOUS
        );
        $this->assertEquals('Lussen\Sie\Tussen', $definition->getClass());
        $this->assertEquals(ExecutionStrategy::STRATEGY_SYNCHRONOUS, $definition->getStrategy());
    }

    /**
     * @test
     */
    public function invalidStrategyThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            "Command does not support execution strategy 'consummatore'"
        );

        $definition = new CommandDefinition(
            'Lussen\Sie\Tussen',
            'consummatore'
        );
    }
}
