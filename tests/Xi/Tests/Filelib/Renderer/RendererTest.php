<?php

namespace Xi\Filelib\Renderer;



class RendererTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Renderer\Renderer'));
    }
    
    
}

