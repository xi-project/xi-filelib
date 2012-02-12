<?php

namespace Xi\Tests\Filelib\Cache;

use Xi\Tests\Filelib\TestCase;

class MockCacheTest extends TestCase
{
    
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Cache\MockCache'));
    }
    
    
}