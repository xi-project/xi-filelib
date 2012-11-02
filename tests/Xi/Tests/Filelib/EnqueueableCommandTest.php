<?php

namespace Xi\Tests\Filelib;

class EnqueueableCommandTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\EnqueueableCommand'));
        $this->assertContains('Xi\Filelib\Queue\Enqueueable', class_implements('Xi\Filelib\EnqueueableCommand'));
        $this->assertContains('Xi\Filelib\Command', class_implements('Xi\Filelib\EnqueueableCommand'));
        $this->assertContains('Serializable', class_implements('Xi\Filelib\EnqueueableCommand'));
    }

}

