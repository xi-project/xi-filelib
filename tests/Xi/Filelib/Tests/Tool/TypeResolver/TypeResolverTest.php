<?php

namespace Xi\Filelib\Tests\Tool\TypeResolver;

class TypeResolverTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Tool\TypeResolver\TypeResolver'));
    }

}
