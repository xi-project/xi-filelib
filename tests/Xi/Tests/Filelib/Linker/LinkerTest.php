<?php

namespace Xi\Tests\Filelib\Linker;

use Xi\Tests\Filelib\TestCase;

class LinkerTest extends TestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Linker\Linker'));
    }
    
    
}