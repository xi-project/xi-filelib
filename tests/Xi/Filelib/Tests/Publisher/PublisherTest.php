<?php

namespace Xi\Filelib\Tests\Publisher;

use Xi\Filelib\Tests\TestCase;

class PublisherTest extends TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Publisher\Publisher'));
    }
}
