<?php

namespace Xi\Tests\Filelib\Publisher;

use Xi\Tests\Filelib\TestCase;

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