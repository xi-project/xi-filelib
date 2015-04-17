<?php

namespace Xi\Filelib\Tests\Asynchrony\ExecutionStrategy;

class ExecutionStrategyTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Asynchrony\ExecutionStrategy\ExecutionStrategy'));
    }
}
