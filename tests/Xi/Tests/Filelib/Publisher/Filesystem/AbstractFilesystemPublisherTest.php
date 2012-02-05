<?php

namespace Xi\Tests\Filelib\Publisher\Filesystem;


use Xi\Filelib\File\FileItem;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher;

use Xi\Tests\Filelib\TestCase;

class AbstractFilesystemPublisherTest extends TestCase
{

    
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkCorrectly()
    {
        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
        ->setMethods(array(
            'publish',
            'unpublish',
            'publishVersion',
            'unpublishVersion',
            'getUrl',
            'getUrlVersion',
            'getFilelib',
            'setFilelib'
        ))
        ->getMock();
        
        $this->assertEquals(0700, $publisher->getDirectoryPermission());
        $this->assertEquals(0600, $publisher->getFilePermission());
        $this->assertEquals(null, $publisher->getPublicRoot());
        $this->assertEquals('', $publisher->getBaseUrl());
        
        // 777 permissions always help!1!
        $dirPerm = "777";
        $filePerm = "666";

        $publicRoot = ROOT_TESTS . '/data/publisher/public';
        $baseUrl = 'http://dr-kobros.com/files';
        
        
        $publisher->setDirectoryPermission($dirPerm);
        $publisher->setFilePermission($filePerm);
        $publisher->setPublicRoot($publicRoot);
        $publisher->setBaseUrl($baseUrl);
        
                
        $this->assertEquals(0777, $publisher->getDirectoryPermission());
        $this->assertEquals(0666, $publisher->getFilePermission());
        $this->assertEquals($publicRoot, $publisher->getPublicRoot());
        $this->assertEquals($baseUrl, $publisher->getBaseUrl());
        
        
        
        
        
    }
    
    
    /**
     * @test
     */
    public function getUrlShouldReturnCorrectUrl()
    {

        $linker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();
        $linker->expects($this->once())->method('getLink')
                ->will($this->returnCallback(function($file) { return 'tussin/lussun/tussi.jpg'; }));
        
        $profileObject = $this->getMockBuilder('Xi\Filelib\File\FileProfile')
                                ->getMock();
        $profileObject->expects($this->once())->method('getLinker')
                      ->will($this->returnCallback(function() use ($linker) { return $linker; }));
                  
        
        $file = $this->getMockBuilder('Xi\Filelib\File\FileItem')->getMock();
        
        $file->expects($this->once())->method('getProfileObject')
                ->will($this->returnCallback(function() use ($profileObject) { return $profileObject; }));
        
        $file->expects($this->any())->method('getId')->will($this->returnValue(1));

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
        ->setMethods(array(
            'publish',
            'unpublish',
            'publishVersion',
            'unpublishVersion',
            'getFilelib',
            'setFilelib'
        ))
        ->getMock();
        
        $publisher->setBaseUrl('http://diktaattoriporssi.com');
        
        $this->assertEquals('http://diktaattoriporssi.com/tussin/lussun/tussi.jpg', $publisher->getUrl($file));
        
                      
    }
    
    /**
     * @test
     */
    public function getUrlVersionShouldReturnCorrectUrlVersion()
    {

        $linker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();
        $linker->expects($this->once())->method('getLinkVersion')
                ->will($this->returnCallback(function($file, $version) { return 'tussin/lussun/tussi-' . $version->getIdentifier() . '.jpg'; }));
        
        $profileObject = $this->getMockBuilder('Xi\Filelib\File\FileProfile')
                                ->getMock();
        $profileObject->expects($this->once())->method('getLinker')
                      ->will($this->returnCallback(function() use ($linker) { return $linker; }));
                  
        
        $file = $this->getMockBuilder('Xi\Filelib\File\FileItem')->getMock();
        
        $file->expects($this->once())->method('getProfileObject')
                ->will($this->returnCallback(function() use ($profileObject) { return $profileObject; }));
        
        $file->expects($this->any())->method('getId')->will($this->returnValue(1));

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
        ->setMethods(array(
            'publish',
            'unpublish',
            'publishVersion',
            'unpublishVersion',
            'getFilelib',
            'setFilelib'
        ))
        ->getMock();
        
        $versionProvider = $this->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\VersionProvider')->getMock();
        $versionProvider->expects($this->once())->method('getIdentifier')
        ->will($this->returnCallback(function() { return 'xooxer'; }));
                
        $publisher->setBaseUrl('http://diktaattoriporssi.com');
        
        $this->assertEquals('http://diktaattoriporssi.com/tussin/lussun/tussi-xooxer.jpg', $publisher->getUrlVersion($file, $versionProvider));
        
                      
    }
    
    
    
    /**
     * @test
     */
    public function setPublicRootShouldThrowExceptionWhenDirectoryDoesNotExist()
    {
       $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
        ->setMethods(array(
            'publish',
            'unpublish',
            'publishVersion',
            'unpublishVersion',
            'getFilelib',
            'setFilelib'
        ))
        ->getMock();
       
        $unexistingDir = ROOT_TESTS . '/data/publisher/unexisting_dir';
        
        try {
            $publisher->setPublicRoot($unexistingDir);
            
            $this->fail("Expected \LogicException!");
            
        } catch (\LogicException $e) {
            
            $this->assertRegExp("/does not exist/", $e->getMessage());
            
        }

        
    }
    
    
    /**
     * @test
     */
    public function setPublicRootShouldThrowExceptionWhenDirectoryIsNotReadable()
    {
       $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
        ->setMethods(array(
            'publish',
            'unpublish',
            'publishVersion',
            'unpublishVersion',
            'getFilelib',
            'setFilelib'
        ))
        ->getMock();
       
        $unwritableDir = ROOT_TESTS . '/data/publisher/unwritable_dir';
        
        try {
            $publisher->setPublicRoot($unwritableDir);
            
            $this->fail("Expected \LogicException!");
            
        } catch (\LogicException $e) {
            
            $this->assertRegExp("/not writeable/", $e->getMessage());
            
        }
        
        
    }
    

    
    
}

?>
