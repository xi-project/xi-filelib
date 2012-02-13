<?php

namespace Xi\Tests\Filelib;

use Xi\Tests\Filelib\TestCase as FilelibTestCase;

class ExceptionTest extends FilelibTestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Exception'));
    }
    
    
}