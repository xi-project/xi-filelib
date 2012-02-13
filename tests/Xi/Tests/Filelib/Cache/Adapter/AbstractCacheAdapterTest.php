<?php

namespace Xi\Tests\Filelib\Cache\Adapter;

use Xi\Tests\Filelib\TestCase;

class AbstractCacheAdapterTest extends TestCase
{
    
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Cache\Adapter\AbstractCacheAdapter'));
    }
    
    
}