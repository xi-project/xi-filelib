<?php

use \Xi\Filelib\Storage\AmazonS3Storage,
    \Xi\Filelib\File\FileItem
    ;

class AmazonS3StorageTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     * @var AmazonS3Storage
     */
    protected $storage;
        
    protected $file;
    
    protected $versionProvider;
    
    protected $fileResource;
    
    protected $filelib;

    
    public function setUp()
    {
        
        if (!class_exists('\\Zend_Service_Amazon_S3') || true) {
            $this->markTestSkipped('Zend_Service_Amazon_S3 class could not be loaded');
        }
                
        $this->fileResource = realpath(ROOT_TESTS . '/data') . '/self-lussing-manatee.jpg';
                        
        $this->filelib = $this->getFilelib();
               
        $storage = new AmazonS3Storage();
        $storage->setKey(S3_KEY);
        $storage->setSecretKey(S3_SECRETKEY);
        $storage->setBucket(S3_BUCKET);
                       
        
        $this->storage = $storage;
        
        $vp = $this->getMock('\Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $vp->expects($this->any())
             ->method('getIdentifier')
             ->will($this->returnValue('xoo'));
        
        $dc = $this->getMock('\Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->any())
            ->method('calculateDirectoryId')
            ->will($this->returnValue('1'));
        
        $this->versionProvider = $vp;
        
        $this->file = \Xi\Filelib\File\FileItem::create(array('id' => 1, 'folder_id' => 1, 'name' => 'self-lussing-manatee.jpg'));
        
                
        
        
        
    }
    
    public function tearDown()
    {
        if (!class_exists('\\Zend_Service_Amazon_S3') || true) {
            return;
        }
        
        $this->storage->getAmazonService()->cleanBucket($this->storage->getBucket());
        
    }

    
    /**
     * @test
     */
    public function storeAndRetrieveAndDeleteShouldWorkSeamlessly()
    {
        $this->storage->setFilelib($this->getFilelib()); 
        $this->storage->store($this->file, $this->fileResource);
               
        $retrieved = $this->storage->retrieve($this->file);
        $this->assertInstanceof('\Xi\Filelib\File\FileObject', $retrieved);
        $this->assertFileEquals($this->fileResource, $retrieved->getRealPath());
         
        $this->storage->delete($this->file);
        
        $ret = $this->storage->getAmazonService()->isObjectAvailable($this->storage->getPath($this->file));

                
        $this->assertFalse($ret);
         
    }
    
    /**
     * @test
     */
    public function storeAndRetrieveAndDeleteVersionShouldWorkSeamlessly()
    {
        $this->storage->setFilelib($this->getFilelib()); 
        $this->storage->storeVersion($this->file, $this->versionProvider, $this->fileResource);
               
        $retrieved = $this->storage->retrieveVersion($this->file, $this->versionProvider);
        $this->assertInstanceof('\Xi\Filelib\File\FileObject', $retrieved);
                 
        $this->storage->deleteVersion($this->file, $this->versionProvider);
                
        $ret = $this->storage->getAmazonService()->isObjectAvailable($this->storage->getPath($this->file) . '_' . $this->versionProvider->getIdentifier());
                
        $this->assertFalse($ret);
         
    }
    
    
}


