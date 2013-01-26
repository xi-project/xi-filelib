<?php

namespace Xi\Filelib\Tests\Queue;

class QueueTest extends \Xi\Filelib\Tests\TestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Queue\Queue'));
    }
    
    
}

