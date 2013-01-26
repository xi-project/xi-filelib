<?php

namespace Xi\Filelib\Tests;

class FilelibExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\FilelibException'));
        
        $this->assertArrayHasKey('Xi\Filelib\Exception', class_implements('Xi\Filelib\FilelibException'));

    }
    
    
}
