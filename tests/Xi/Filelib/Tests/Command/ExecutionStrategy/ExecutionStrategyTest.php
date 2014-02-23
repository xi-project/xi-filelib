<?php

namespace Xi\Filelib\Tests\Command\ExecutionStrategy;

class ExecutionStrategyTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy'));
    }
}
