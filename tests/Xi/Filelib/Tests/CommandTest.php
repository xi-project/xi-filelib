<?php

namespace Xi\Filelib\Tests;

class CommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Command'));
    }

}
