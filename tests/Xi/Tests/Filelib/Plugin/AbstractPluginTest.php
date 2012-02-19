<?php

namespace Xi\Tests\Filelib\Plugin;

use Xi\Tests\Filelib\TestCase;
use Xi\Filelib\File\Upload\FileUpload;

use Xi\Filelib\Plugin\AbstractPlugin;

class AbstractPluginTest extends TestCase
{
 
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Plugin\AbstractPlugin'));
        $this->assertContains('Xi\Filelib\Plugin\Plugin', class_implements('Xi\Filelib\Plugin\AbstractPlugin'));
    }
    
    
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\AbstractPlugin')->setMethods(array())->getMockForAbstractClass();
        
        $this->assertEquals(array(), $plugin->getProfiles());
        $this->assertNull($plugin->getFilelib());
        
        $profiles = array('tussin', 'lussutus');
        
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        
        $this->assertEquals($plugin, $plugin->setProfiles($profiles));
        $this->assertEquals($plugin, $plugin->setFilelib($filelib));
        
        $this->assertEquals($profiles, $plugin->getProfiles());
        $this->assertEquals($filelib, $plugin->getFilelib());
    }
    
    /**
     * @test
     */
    public function emptyHooksShouldBeCallableAndReturnExpectedValues()
    {
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\AbstractPlugin')->setMethods(array())->getMockForAbstractClass();
        
        $this->assertEquals($upload, $plugin->beforeUpload($upload));
        
        $file = $this->getMockForAbstractClass('Xi\Filelib\File\FileItem');
        
        $this->assertNull($plugin->afterUpload($file));
        $this->assertNull($plugin->init());
        $this->assertNull($plugin->onDelete($file));
        $this->assertNull($plugin->onPublish($file));
        $this->assertNull($plugin->onUnpublish($file));
    }
    
    
    /**
     * @test
     */
    public function getSubscribedEventsShouldReturnEmptyArray()
    {
        $subscribedEvents = AbstractPlugin::getSubscribedEvents();
        $this->assertEquals(array(), $subscribedEvents);
    }
}
