<?php

namespace Xi\Filelib\Tests\Renderer;

class RendererTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Renderer\Renderer'));
    }

}
