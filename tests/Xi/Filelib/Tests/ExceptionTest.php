<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Tests\TestCase as FilelibTestCase;

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
