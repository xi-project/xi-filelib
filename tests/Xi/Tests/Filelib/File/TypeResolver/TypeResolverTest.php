<?php

namespace Xi\Tests\Filelib\File\TypeResolver;

use Xi\Tests\Filelib\TestCase as FilelibTestCase;

class FileTest extends FilelibTestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\File\TypeResolver\TypeResolver'));
    }
    
    
}