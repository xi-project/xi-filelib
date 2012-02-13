<?php

namespace Xi\Tests\Filelib\Acl;

use Xi\Tests\Filelib\TestCase as FilelibTestCase;

class AclTest extends FilelibTestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Acl\Acl'));
    }
    
    
}