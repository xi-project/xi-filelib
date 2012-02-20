<?php

namespace Xi\Tests\Filelib\Acl;

class AclTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Acl\Acl'));
    }
    
    
}