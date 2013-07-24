<?php

namespace Xi\Filelib\Tests\Authorization;

class AuthorizationAdapterTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertInterfaceExists('Xi\Filelib\Authorization\AuthorizationAdapter');
    }

}
