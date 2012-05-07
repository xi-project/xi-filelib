<?php

namespace Xi\Tests\Filelib\Queue;

class QueueTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Queue\Queue'));
    }
    
    
}

