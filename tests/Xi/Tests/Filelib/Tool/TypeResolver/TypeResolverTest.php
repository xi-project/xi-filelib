<?php

namespace Xi\Tests\Filelib\Tool\TypeResolver;

class TypeResolverTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Tool\TypeResolver\TypeResolver'));
    }


}

