<?php

namespace Xi\Tests\Filelib\Tool\UuidGenerator;

class UuidGeneratorTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Tool\UuidGenerator\UuidGenerator'));
    }


}

