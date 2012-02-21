<?php

namespace Xi\Filelib\Renderer;

use Xi\Filelib\Renderer\SymfonyRenderer;
use Xi\Filelib\File\FileItem;
use Symfony\Component\HttpFoundation\Response;
use Xi\Filelib\File\FileObject;

class SymfonyRendererTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Renderer\SymfonyRenderer'));
        $this->assertContains('Xi\Filelib\Renderer\AcceleratedRenderer', class_implements('Xi\Filelib\Renderer\SymfonyRenderer'));
    }
    
    /**
     * @test
     */
    public function mergeOptionsShouldReturnSanitizedResult()
    {
        
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $renderer = new SymfonyRenderer($fiop);
        
        $expected = array(
            'version' => 'original',
            'download' => false,
        );
        
        $options = array();
        
        $this->assertEquals($expected, $renderer->mergeOptions($options));
        
        $expected = array(
            'version' => 'orignaluss',
            'download' => false,
            'impossible' => 'impossibru'
        );
        
        $options = array(
            'version' => 'orignaluss',
            'impossible' => 'impossibru',
        );
        
        $this->assertEquals($expected, $renderer->mergeOptions($options));
        
        
                
    }
    
    /**
     * @test
     */
    public function getPublisherShouldDelegateToFileOperator()
    {
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $renderer = new SymfonyRenderer($fiop);
        
        $fiop->expects($this->once())->method('getPublisher');
       
        $renderer = $renderer->getPublisher();
    }
    
    
    /**
     * @test
     */
    public function getAclShouldDelegateToFileOperator()
    {
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $renderer = new SymfonyRenderer($fiop);
        
        $fiop->expects($this->once())->method('getAcl');
       
        $acl = $renderer->getAcl();
    }
    
    
    /**
     * @test
     */
    public function getStorageShouldDelegateToFileOperator()
    {
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $renderer = new SymfonyRenderer($fiop);
        
        $fiop->expects($this->once())->method('getStorage');
       
        $acl = $renderer->getStorage();
    }

    /**
     * @test
     */
    public function getUrlShouldDelegateToPublisherWhenUsingOriginalVersion()
    {
        $file = FileItem::create(array('id' => 1));
        
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher'))
                         ->setConstructorArgs(array($fiop))
                         ->getMock();
        
        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $publisher->expects($this->once())->method('getUrl')->with($this->equalTo($file));
        
        $renderer->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));
        
        $url = $renderer->getUrl($file, array('version' => 'original'));
        
        
        
    }
    

    /**
     * @test
     */
    public function getUrlShouldDelegateToPublisherWhenUsingNonOriginalVersion()
    {
        $file = FileItem::create(array('id' => 1));
        
        $vp = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $fiop->expects($this->once())->method('getVersionProvider')
             ->with($this->equalTo($file), $this->equalTo('lussen'))
             ->will($this->returnValue($vp));
        
        
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher'))
                         ->setConstructorArgs(array($fiop))
                         ->getMock();
        
        $publisher = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $publisher->expects($this->once())->method('getUrlVersion')->with($this->equalTo($file), $this->equalTo($vp));
        
        $renderer->expects($this->any())->method('getPublisher')->will($this->returnValue($publisher));
        
        $url = $renderer->getUrl($file, array('version' => 'lussen'));
        
    }
    
    /**
     * @test
     */
    public function responseShouldBe403WhenAclForbidsRead()
    {
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher', 'getAcl'))
                         ->setConstructorArgs(array($fiop))
                         ->getMock();
        
        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('fileIsReadable')->will($this->returnValue(false));
        
        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        
        
        $file = FileItem::create(array('id' => 1));
                
        $response = $renderer->render($file);
        
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());
                
    }
    
    
    
    /**
     * @test
     */
    public function responseShouldBe403WhenProfileForbidsReadOfOriginalFile()
    {
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher', 'getAcl'))
                         ->setConstructorArgs(array($fiop))
                         ->getMock();
        
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(false));
        
        $fiop->expects($this->any())->method('getProfile')->will($this->returnValue($profile));
        
        
        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));
        
        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        
        $file = FileItem::create(array('id' => 1));
                
        $response = $renderer->render($file);
        
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());
                
    }

    /**
     * @test
     */
    public function responseShouldBeCorrectWhenProfileAllowsReadOfOriginalFileAndDownloadIsFalse()
    {
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher', 'getAcl', 'getStorage'))
                         ->setConstructorArgs(array($fiop))
                         ->getMock();
        
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(true));
        
        $retrieved = new FileObject($path);
        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('retrieve')->will($this->returnValue($retrieved));
        
        $fiop->expects($this->any())->method('getProfile')->will($this->returnValue($profile));
        
        
        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));
        
        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        $renderer->expects($this->any())->method('getStorage')->will($this->returnValue($storage));
        
        $file = FileItem::create(array('id' => 1));
                
        $response = $renderer->render($file);
        
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals(file_get_contents($path), $response->getContent());
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
        
        
    }
    

    /**
     * @test
     */
    public function responseShouldBeCorrectWhenProfileAllowsReadOfOriginalFileAndDownloadIsTrue()
    {
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher', 'getAcl', 'getStorage'))
                         ->setConstructorArgs(array($fiop))
                         ->getMock();
        
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');
        $profile->expects($this->atLeastOnce())->method('getAccessToOriginal')->will($this->returnValue(true));
        
        $retrieved = new FileObject($path);
        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('retrieve')->will($this->returnValue($retrieved));
        
        $fiop->expects($this->any())->method('getProfile')->will($this->returnValue($profile));
        
        
        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));
        
        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        $renderer->expects($this->any())->method('getStorage')->will($this->returnValue($storage));
        
        $file = FileItem::create(array('id' => 1, 'name' => 'self-lusser.lus'));
                
        $response = $renderer->render($file, array('download' => true));
        
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals(file_get_contents($path), $response->getContent());
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
        
        $this->assertEquals("attachment; filename=self-lusser.lus", $response->headers->get('Content-disposition'));
        
    }

    
    
    /**
     * @test
     */
    public function responseShouldBe404WhenVersionDoesNotExist()
    {
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher', 'getAcl'))
                         ->setConstructorArgs(array($fiop))
                         ->getMock();
        
        $fiop->expects($this->any())->method('hasVersion')->will($this->returnValue(false));
                
        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->any())->method('isFileReadable')->will($this->returnValue(true));
        
        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        
        $file = FileItem::create(array('id' => 1));
                
        $response = $renderer->render($file, array('version' => 'lussenhofer'));
        
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());
                
    }
    
    /**
     * @test
     */
    public function responseShouldBeCorrectWhenVersionDoesExist()
    {
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        
        $retrieved = new FileObject($path); 
        
        $file = FileItem::create(array('id' => 1));
        
        $fiop = $this->getMockForAbstractClass('Xi\Filelib\File\FileOperator');
        $renderer = $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                         ->setMethods(array('getPublisher', 'getAcl', 'getStorage'))
                         ->setConstructorArgs(array($fiop))
                         ->getMock();
        
        $vp = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        
        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('retrieveVersion')
                ->with($this->equalTo($file), $this->equalTo($vp))
                ->will($this->returnValue($retrieved));
                
        $fiop->expects($this->atLeastOnce())->method('getVersionProvider')
             ->with($this->equalTo($file), $this->equalTo('lussenhofer'))
             ->will($this->returnValue($vp));
          
        $fiop->expects($this->any())->method('hasVersion')->will($this->returnValue(true));
                
        $acl = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        $acl->expects($this->atLeastOnce())->method('isFileReadable')->will($this->returnValue(true));
        
        $renderer->expects($this->any())->method('getAcl')->will($this->returnValue($acl));
        $renderer->expects($this->any())->method('getStorage')->will($this->returnValue($storage));
                
        $response = $renderer->render($file, array('version' => 'lussenhofer'));
        
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals(file_get_contents($path), $response->getContent());
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
                
    }
    
}

