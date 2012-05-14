<?php

namespace Xi\Tests\Filelib\Queue\Processor;

class QueueProcessorTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Queue\Processor\QueueProcessor'));
    }


}

