<?php

namespace Xi\Tests\Filelib\Backend;

use Xi\Tests\Filelib\TestCase as FilelibTestCase;

class BackendTest extends FilelibTestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Backend\Backend'));
    }
    
    
}