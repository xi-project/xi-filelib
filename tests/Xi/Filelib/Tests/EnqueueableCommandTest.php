<?php

namespace Xi\Filelib\Tests;

class EnqueueableCommandTest extends \Xi\Filelib\Tests\TestCase
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
