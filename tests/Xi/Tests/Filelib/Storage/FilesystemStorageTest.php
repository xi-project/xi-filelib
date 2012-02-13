<?php

namespace Xi\Tests\Filelib\Storage;

class FilesystemStorageTest extends \Xi\Tests\Filelib\TestCase
{
       
    
    protected $storage;
        
    protected $file;
    
    protected $versionProvider;

    protected $fileResource;
        
    
    protected function setUp()
    {
        
                
        $this->file = \Xi\Filelib\File\FileItem::create(array('id' => 1));
        
        $this->fileResource = realpath(ROOT_TESTS . '/data') . '/self-lussing-manatee.jpg';
                        
        $dc = $this->getMock('\Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->any())
             ->method('calculateDirectoryId')
             ->will($this->returnValue('1'));
       
        
        
        $storage = new \Xi\Filelib\Storage\FilesystemStorage();
        $storage->setDirectoryIdCalculator($dc);
        $storage->setCacheDirectoryIds(false);
        $storage->setRoot(ROOT_TESTS . '/data/files');    
        
        $this->storage = $storage;
        
        
        
        $vp = $this->getMock('\Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $vp->expects($this->any())
             ->method('getIdentifier')
             ->will($this->returnValue('xoo'));
        
        $this->versionProvider = $vp;
             
        
    }
        
    protected function tearDown()
    {
                        
        $diter = new \RecursiveDirectoryIterator($this->storage->getRoot());
        $riter = new \RecursiveIteratorIterator($diter, \RecursiveIteratorIterator::CHILD_FIRST);
        
        foreach ($riter as $item) {
            if($item->isFile() && $item->getFilename() !== '.gitignore') {
                @unlink($item->getPathName());
            }            
        }
        
        foreach ($riter as $item) {
            if($item->isDir() && !in_array($item->getPathName(), array('.', '..'))) {
                @rmdir($item->getPathName());
            }            
        }
        
    }
    
    /**
     * @test
     */
    public function filePermissionGetAndSetShouldWorkAsExpected()
    {
        $this->assertEquals(0600, $this->storage->getFilePermission());
        $this->storage->setFilePermission(755);
        $this->assertEquals(0755, $this->storage->getFilePermission());
    }

    /**
     * @test
     */
    public function rootGetAndSetShouldWorkAsExpected()
    {
        $storage = new \Xi\Filelib\Storage\FilesystemStorage();
        $this->assertNull($storage->getRoot());
        $storage->setRoot(ROOT_TESTS . '/data');     

        $this->assertEquals(ROOT_TESTS . '/data', $storage->getRoot());
        
    }
    
    
    /**
     * @test
     */
    public function directoryPermissionGetAndSetShouldWorkAsExpected()
    {
        $this->assertEquals(0700, $this->storage->getDirectoryPermission());
        $this->storage->setDirectoryPermission(755);
        $this->assertEquals(0755, $this->storage->getDirectoryPermission());
    }
    
    
    /**
     * @test
     */
    public function directoryCalculatorGetAndSetShouldWorkAsExpected()
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
    
    
    /**
     * @test
     */
    public function directoryIdCalculationWithoutCachingShouldCallMethodEveryTime()
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
       
    /**
     * @test
     */
    public function directoryIdCalculationWithCachingShouldCallMethodOnlyOnce()
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
    
    
    /**
     * @test
     */
    public function storeAndRetrieveAndDeleteShouldWorkInHarmony()
    {
         $this->storage->store($this->file, $this->fileResource);
         
         $this->assertFileExists($this->storage->getRoot() . '/1/1');
         $this->assertFileEquals($this->fileResource, $this->storage->getRoot() . '/1/1');
         
         $retrieved = $this->storage->retrieve($this->file);
         $this->assertInstanceof('\Xi\Filelib\File\FileObject', $retrieved);
         $this->assertFileEquals($this->fileResource, $retrieved->getRealPath());
         
         $this->storage->delete($this->file);
         $this->assertFalse(file_exists($this->storage->getRoot() . '/1/1'));
         
    }
    
    /**
     * @test
     */
    public function versionStoreAndRetrieveAndDeleteShouldWorkInHarmony()
    {
         $this->storage->storeVersion($this->file, $this->versionProvider, $this->fileResource);
         
         $this->assertFileExists($this->storage->getRoot() . '/1/xoo/1');
         $this->assertFileEquals($this->fileResource, $this->storage->getRoot() . '/1/xoo/1');
                  
         $retrieved = $this->storage->retrieveVersion($this->file, $this->versionProvider);
         $this->assertInstanceof('\Xi\Filelib\File\FileObject', $retrieved);
         $this->assertFileEquals($this->fileResource, $retrieved->getRealPath());
                  
         $this->storage->deleteVersion($this->file, $this->versionProvider);
         $this->assertFalse(file_exists($this->storage->getRoot() . '/1/xoo/1'));
    }
    
    
    
    
}
 