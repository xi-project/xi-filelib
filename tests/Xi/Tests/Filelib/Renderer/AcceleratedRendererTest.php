<?php

namespace Xi\Tests\Filelib\Renderer;

class AcceleratedRendererTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Renderer\AcceleratedRenderer'));
        $this->assertContains('Xi\Filelib\Renderer\Renderer', class_implements('Xi\Filelib\Renderer\AcceleratedRenderer'));
    }
    
    
}

