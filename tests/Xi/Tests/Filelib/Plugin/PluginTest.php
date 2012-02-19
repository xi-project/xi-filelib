<?php

namespace Xi\Tests\Filelib\Plugin;

use Xi\Tests\Filelib\TestCase;

class PluginTest extends TestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        
        $this->assertTrue(interface_exists('Xi\Filelib\Plugin\Plugin'));
        $this->assertContains('Symfony\Component\EventDispatcher\EventSubscriberInterface', class_implements('Xi\Filelib\Plugin\Plugin'));
    }
    
    
}