<?php

namespace Xi\Filelib\Renderer;

class SymfonyRendererTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Renderer\SymfonyRenderer'));
        $this->assertContains('Xi\Filelib\Renderer\Renderer', class_implements('Xi\Filelib\Renderer\SymfonyRenderer'));
    }
    
}

