<?php

namespace Xi\Tests\Filelib;

class CommandTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Command'));
        $this->assertContains('Xi\Filelib\Queue\Enqueueable', class_implements('Xi\Filelib\Command'));

    }

}

