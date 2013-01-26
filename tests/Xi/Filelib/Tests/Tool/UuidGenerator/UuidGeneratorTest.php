<?php

namespace Xi\Filelib\Tests\Tool\UuidGenerator;

class UuidGeneratorTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Tool\UuidGenerator\UuidGenerator'));
    }


}

