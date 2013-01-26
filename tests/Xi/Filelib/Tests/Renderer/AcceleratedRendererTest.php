<?php

namespace Xi\Filelib\Tests\Renderer;

class AcceleratedRendererTest extends \Xi\Filelib\Tests\TestCase
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

