<?php

namespace Xi\Filelib\Tests\Queue;

use Xi\Filelib\Tests\TestCase;

class UuidReceiverTest extends TestCase
{
    /**
     * @test
     */
    public function interfaceExists()
    {
        $this->assertInterfaceExists('Xi\Filelib\Queue\UuidReceiver');
    }
}
