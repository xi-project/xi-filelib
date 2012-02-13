<?php

namespace Xi\Tests\Filelib\Cache;

use Xi\Tests\Filelib\TestCase;

class CacheTest extends TestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Cache\Cache'));
    }
    
    
}