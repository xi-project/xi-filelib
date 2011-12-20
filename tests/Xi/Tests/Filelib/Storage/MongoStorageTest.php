<?php

namespace Xi\Tests\Filelib\Storage;

use \Mongo, \MongoDB;

class MongoStorageTest extends \Xi\Tests\Filelib\TestCase
{

    protected $mongo;
        
    protected $storage;
        
    protected $file;
    
    protected $versionProvider;
    
    protected $fileResource;
    
    protected $filelib;
        
    
    protected function setUp()
    {
        
        $this->file = \Xi\Filelib\File\FileItem::create(array('id' => 1));
        
        $this->fileResource = realpath(ROOT_TESTS . '/data') . '/self-lussing-manatee.jpg';
        
                
        $this->filelib = $this->getMockFilelib();
        
        $dns = "mongodb://localhost:27017/";
        $mongo = new Mongo($dns, array('connect' => true));
        $this->mongo = $mongo->filelib_tests;    
               
        $storage = new \Xi\Filelib\Storage\GridfsStorage();
        $storage->setMongo($this->mongo);
        
        $this->storage = $storage;
        
        $vp = $this->getMock('\Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $vp->expects($this->any())
             ->method('getIdentifier')
             ->will($this->returnValue('xoo'));
        
        $this->versionProvider = $vp;

        $list = $this->mongo->listCollections();
        foreach ($list as $collection) {
            $collection->drop();
        }        
        
    }
    
    
    protected function tearDown()
    {
        $list = $this->mongo->listCollections();
        foreach ($list as $collection) {
            $collection->drop();
        }        
    }
    
    public function testPrefixSetAndGet()
    {
        $this->assertEquals(null, $this->storage->getPrefix());
        $this->storage->setPrefix('luss');
        $this->assertEquals('luss', $this->storage->getPrefix());

    }

    
    public function ytestRootGetAndSet()
    {
        $storage = new \Xi\Filelib\Storage\FilesystemStorage();
        $this->assertNull($storage->getRoot());
        $storage->setRoot(ROOT_TESTS . '/data');     

        $this->assertEquals(ROOT_TESTS . '/data', $storage->getRoot());
        
    }
    
    
    
    public function ytestDirectoryPermissionGetAndSet()
    {
        $this->assertEquals(0700, $this->storage->getDirectoryPermission());
        $this->storage->setDirectoryPermission(755);
        $this->assertEquals(0755, $this->storage->getDirectoryPermission());
    }
    
    
    public function ytestDirectoryCalculatorGetAndSet()
    {
         $storage = new \Xi\Filelib\Storage\FilesystemStorage();

         $dc = $this->getMock('\Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
         $dc->expects($this->any())
             ->method('calculateDirectoryId')
             ->will($this->returnValue('1'));
             
         $this->assertNull($storage->getDirectoryIdCalculator());

         $storage->setDirectoryIdCalculator($dc);
         
         $this->assertEquals($dc, $storage->getDirectoryIdCalculator());
         
         
         
    }
    
    
    
    public function ytestDirectoryIdCalculationWithoutCaching()
    {
        $dc = $this->getMock('\Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->exactly(3))
             ->method('calculateDirectoryId')
             ->will($this->returnValue('1'));
                
        $this->storage->setDirectoryIdCalculator($dc);
             
        $this->assertFalse($this->storage->getCacheDirectoryIds());
                        
        $this->assertEquals(1, $this->storage->getDirectoryId($this->file));
        $this->assertEquals(1, $this->storage->getDirectoryId($this->file));
        $this->assertEquals(1, $this->storage->getDirectoryId($this->file));
        
    }
       
    
    public function ytestDirectoryIdCalculationWithCaching()
    {
        $dc = $this->getMock('\Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->exactly(1))
             ->method('calculateDirectoryId')
             ->will($this->returnValue('1'));
                
        $this->storage->setDirectoryIdCalculator($dc);
             
        $this->assertFalse($this->storage->getCacheDirectoryIds());
        $this->storage->setCacheDirectoryIds(true);
        $this->assertTrue($this->storage->getCacheDirectoryIds());
                        
        $this->assertEquals(1, $this->storage->getDirectoryId($this->file));
        $this->assertEquals(1, $this->storage->getDirectoryId($this->file));
        $this->assertEquals(1, $this->storage->getDirectoryId($this->file));
        
    }
    
    
    
    public function ytestStoreAndRetrieveAndDelete()
    {
         $this->storage->store($this->file, $this->fileResource);
         
         $this->assertFileExists($this->storage->getRoot() . '/1/1');
         $this->assertFileEquals($this->fileResource, $this->storage->getRoot() . '/1/1');
         
         $retrieved = $this->storage->retrieve($this->file);
         $this->assertInstanceof('\Xi\Base\Spl\FileObject', $retrieved);
         $this->assertFileEquals($this->fileResource, $retrieved->getRealPath());
         
         $this->storage->delete($this->file);
         $this->assertFalse(file_exists($this->storage->getRoot() . '/1/1'));
         
    }
    

    public function ytestVersionStoreAndRetrieveAndDelete()
    {
         $this->storage->storeVersion($this->file, $this->versionProvider, $this->fileResource);
         
         $this->assertFileExists($this->storage->getRoot() . '/1/xoo/1');
         $this->assertFileEquals($this->fileResource, $this->storage->getRoot() . '/1/xoo/1');
                  
         $retrieved = $this->storage->retrieveVersion($this->file, $this->versionProvider);
         $this->assertInstanceof('\Xi\Base\Spl\FileObject', $retrieved);
         $this->assertFileEquals($this->fileResource, $retrieved->getRealPath());
                  
         $this->storage->deleteVersion($this->file, $this->versionProvider);
         $this->assertFalse(file_exists($this->storage->getRoot() . '/1/xoo/1'));
    }
    
    
    
    
}
 