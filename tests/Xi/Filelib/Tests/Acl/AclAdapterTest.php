<?php

namespace Xi\Filelib\Tests\Acl;

class AclAdapterTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertInterfaceExists('Xi\Filelib\Acl\AclAdapter');
    }

}
