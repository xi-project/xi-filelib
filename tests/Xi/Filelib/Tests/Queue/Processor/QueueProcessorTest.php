<?php

namespace Xi\Filelib\Tests\Queue\Processor;

class QueueProcessorTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Queue\Processor\QueueProcessor'));
    }

}
