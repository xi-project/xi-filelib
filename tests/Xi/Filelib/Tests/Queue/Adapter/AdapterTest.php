<?php

namespace Xi\Filelib\Tests\Queue\Adapter;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Queue\Adapter\Adapter'));
    }

}
