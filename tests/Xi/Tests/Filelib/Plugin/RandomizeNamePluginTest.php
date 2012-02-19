<?php

namespace Xi\Tests\Filelib\Plugin;

use Xi\Tests\Filelib\TestCase;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Plugin\RandomizeNamePlugin;

class RandomizeNamePluginTest extends TestCase
{
    
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $plugin = new RandomizeNamePlugin();
        
        $this->assertEquals('', $plugin->getPrefix());
                
        $prefix = 'tussi';
                        
        $this->assertEquals($plugin, $plugin->setPrefix($prefix));
                
        $this->assertEquals($prefix, $plugin->getPrefix());
    }
    
    
    public function provideOverrideFilenames()
    {
        return array(
            array('tussi', 'tussenhof'),
            array('lus', 'tussenhof'),
            array('k_makkara', 'tussenhof'),
        );
    }
    
    
    /**
     * @test
     */
    public function beforeUploadShouldRandomizeUploadFilename()
    {
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
                        
        $plugin = new RandomizeNamePlugin();
        
        $upload2 = $plugin->beforeUpload($upload);
        
        $this->assertSame($upload, $upload2);
        
        $this->assertNotEquals('self-lussing-manatee', $upload2->getUploadFilename());
        
        $pinfo = pathinfo($upload2->getUploadFilename());
        
        $this->assertArrayHasKey('extension', $pinfo);
        
        $this->assertEquals('jpg', $pinfo['extension']);
        
        $this->assertEquals(27, strlen($upload2->getUploadFilename()));
        
    }
    
    
    /**
     * @test
     */
    public function beforeUploadShouldRandomizeOverriddenUploadFilename()
    {
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $upload->setOverrideFilename('tussinlussuttajankabaali');
        
        $plugin = new RandomizeNamePlugin();
        
        $upload2 = $plugin->beforeUpload($upload);
        
        $this->assertEquals($upload, $upload2);
        
        $this->assertNotEquals('self-lussing-manatee', $upload2->getUploadFilename());
        
        $pinfo = pathinfo($upload2->getUploadFilename());
                        
        $this->assertArrayNotHasKey('extension', $pinfo);
        $this->assertEquals(23, strlen($upload2->getUploadFilename()));
    }
    
    
    public function providePrefixes()
    {
        return array(
            array('tussi'),
            array('helistin'),
            array('bansku'),
            array('johtaja'),
        );
    }
           
    
    
    /**
     * @test
     * @dataProvider providePrefixes
     */
    public function beforeUploadShouldPrefixRandomizedName($prefix)
    {
        $plugin = new RandomizeNamePlugin();
        $plugin->setPrefix($prefix);
        
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        
        $upload2 = $plugin->beforeUpload($upload);
        
        $this->assertStringStartsWith($prefix, $upload2->getUploadFilename());
        $this->assertEquals(27 + strlen($prefix), strlen($upload2->getUploadFilename()));
        
    }
    
    /**
     * @test
     */
    public function getSubscribedEventsShouldReturnCorrectEvents()
    {
        $events = RandomizeNamePlugin::getSubscribedEvents();
        $this->assertArrayHasKey('fileprofile.add', $events);
        $this->assertArrayHasKey('file.beforeUpload', $events);
    }
    
    
}
