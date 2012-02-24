<?php

namespace Xi\Tests\Filelib\Plugin\Image;

use Imagick;
use Xi\Filelib\Plugin\Image\ChangeFormatPlugin;
use Xi\Filelib\Event\FileUploadEvent;

class ChangeFormatPluginTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Plugin\Image\ChangeFormatPlugin'));
        $this->assertArrayHasKey('Xi\Filelib\Plugin\AbstractPlugin', class_parents('Xi\Filelib\Plugin\Image\ChangeFormatPlugin'));
    }
    
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $plugin = new ChangeFormatPlugin();

        $ext = 'jpg';
        $this->assertEquals(null, $plugin->getTargetExtension());
        $this->assertSame($plugin, $plugin->setTargetExtension($ext));
        $this->assertEquals($ext, $plugin->getTargetExtension());
    }

    /**
     * @test
     */
    public function getImageMagickHelperShouldReturnImageMagickHelper()
    {
        $plugin = new ChangeFormatPlugin();
        $plugin->setProfiles(array('tussi'));
        $helper = $plugin->getImageMagickHelper();
        
        $this->assertInstanceOf('Xi\Filelib\Plugin\Image\ImageMagickHelper', $helper);
        
        $this->assertSame($helper, $plugin->getImageMagickHelper());
        
    }
    
    
    /**
     * @test
     */
    public function beforeUploadShouldExitEarlyIfPluginDoesntHaveProfile()
    {
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        
        $event = $this->getMockBuilder('Xi\Filelib\Event\FileUploadEvent')
                      ->disableOriginalConstructor()
                      ->getMock();
        
        $event->expects($this->once())->method('getProfile')->will($this->returnValue($profile));
        
        $event->expects($this->never())->method('getFileUpload');
                        
        $plugin = new ChangeFormatPlugin();
                
        $plugin->beforeUpload($event);
                        

    }

    
    
    /**
     * @test
     */
    public function beforeUploadShouldReturnSameUploadWhenNotImage()
    {
        $upload = $this->getMockBuilder('Xi\Filelib\File\Upload\FileUpload')
                       ->setConstructorArgs(array(ROOT_TESTS . '/data/refcard.pdf'))
                       ->getMock();
                
        $plugin = new ChangeFormatPlugin();
        $plugin->setProfiles(array('tussi'));

        $upload->expects($this->once())->method('getMimeType')->will($this->returnValue('video/lus'));
        
        $folder = $this->getMockForAbstractClass('Xi\Filelib\Folder\Folder');
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('tussi'));
        $event = new FileUploadEvent($upload, $folder, $profile);
        
        $plugin->beforeUpload($event);
        
        $nupload = $event->getFileUpload();
        
        $this->assertSame($upload, $nupload);
        
    }
    
    /**
     * @test
     */
    public function beforeUploadShouldReturnNewUploadWhenImage()
    {
        
        $helper = $this->getMock('Xi\Filelib\Plugin\Image\ImageMagickHelper');
        
        $mock = $this->getMock('Imagick');
        $mock->expects($this->once())->method('writeImage')->with($this->matchesRegularExpression("#^/tmp/dir#"));
        
        $helper->expects($this->once())->method('createImagick')->will($this->returnValue($mock));
        
        $helper->expects($this->once())->method('execute')->with($this->equalTo($mock));
        
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $fileOp = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                       ->setConstructorArgs(array($filelib))
                       ->getMock();
        
                
        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fileOp));
        $filelib->expects($this->any())->method('getTempDir')->will($this->returnValue('/tmp/dir'));
        
        $upload = $this->getMockBuilder('Xi\Filelib\File\Upload\FileUpload')
                       ->setConstructorArgs(array(ROOT_TESTS . '/data/self-lussing-manatee.jpg'))
                       ->getMock();
        
        $upload->expects($this->any())->method('getMimeType')->will($this->returnValue('image/jpeg'));
        $upload->expects($this->atLeastOnce())->method('getUploadFilename')->will($this->returnValue('self-lussing-manatee.jpg'));
                
        $nupload = $this->getMockBuilder('Xi\Filelib\File\Upload\FileUpload')
                       ->setConstructorArgs(array(ROOT_TESTS . '/data/self-lussing-manatee.jpg'))
                       ->getMock();
        $nupload->expects($this->once())->method('setTemporary')->with($this->equalTo(true));
        $nupload->expects($this->once())->method('setOverrideFilename')->with($this->equalTo('self-lussing-manatee.lus'));
        
        $fileOp->expects($this->once())->method('prepareUpload')->will($this->returnValue($nupload));
        
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\Image\ChangeFormatPlugin')
                       ->setMethods(array('getImageMagickHelper'))
                       ->disableOriginalConstructor()
                       ->getMock();
        $plugin->setProfiles(array('tussi'));
        
        $plugin->expects($this->any())->method('getImageMagickHelper')->will($this->returnValue($helper));
                
        $plugin->setTargetExtension('lus');
        $plugin->setFilelib($filelib);
        
        $folder = $this->getMockForAbstractClass('Xi\Filelib\Folder\Folder');
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('tussi'));
        $event = new FileUploadEvent($upload, $folder, $profile);
        
        $plugin->beforeUpload($event);
        
        $xupload = $event->getFileUpload();
        
        $this->assertInstanceOf('Xi\Filelib\File\Upload\FileUpload', $xupload);
        $this->assertNotSame($upload, $xupload);
        
    }
    
    
    /**
     * @test
     */
    public function getSubscribedEventsShouldReturnCorrectEvents()
    {
        $events = ChangeFormatPlugin::getSubscribedEvents();
        $this->assertArrayHasKey('fileprofile.add', $events);
        $this->assertArrayHasKey('file.beforeUpload', $events);
    }

    
}