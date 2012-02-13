<?php

namespace Xi\Tests\Filelib\Storage;

use Mongo,
    MongoDB,
    MongoGridFS,
    MongoCollection,
    MongoConnectionException,
    Xi\Tests\Filelib\TestCase,
    Xi\Filelib\Storage\GridfsStorage,
    Xi\Filelib\File\FileItem,
    Xi\Filelib\FilelibException
    ;

class GridFsStorageTest extends TestCase
{

    /**
     *
     * @var MondoDB
     */
    protected $mongo;
    
    /**
     *
     * @var GridfsStorage
     */
    protected $storage;
        
    protected $file;
    
    protected $versionProvider;
    
    protected $fileResource;
    
    protected $filelib;
        
    
    protected function setUp()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('MongoDB extension is not loaded.');
        }

        try {
            $mongo = new Mongo(MONGO_DNS, array('connect' => true));
        } catch (MongoConnectionException $e) {
            $this->markTestSkipped('Can not connect to MongoDB.');
        }

        $this->file = \Xi\Filelib\File\FileItem::create(array('id' => 1));
        
        $this->fileResource = realpath(ROOT_TESTS . '/data') . '/self-lussing-manatee.jpg';
        
                
        $this->filelib = $this->getFilelib();
        
        $this->mongo = $mongo->filelib_tests;    
               
        $storage = new \Xi\Filelib\Storage\GridfsStorage();
        $storage->setMongo($this->mongo);
        
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
        
                
        $this->fileResource = realpath(ROOT_TESTS . '/data') . '/self-lussing-manatee.jpg';

        
    }
    
    
    protected function tearDown()
    {
        if (extension_loaded('mongo') && $this->mongo) {
            foreach ($this->mongo->listCollections() as $collection) {
                $collection->drop();
            }
        }
        
    }
    
    
    /**
     * @test
     */
    public function prefixSetAndGetShouldWorkAsExcpected()
    {
        $this->assertEquals('xi_filelib', $this->storage->getPrefix());
        $this->storage->setPrefix('luss');
        $this->assertEquals('luss', $this->storage->getPrefix());

    }


    
    /**
     * @test
     */
    public function storeAndRetrieveAndDeleteShouldWorkInHarmony()
    {
        $this->storage->setFilelib($this->getFilelib()); 
        
        $this->storage->store($this->file, $this->fileResource);
         
         $file = $this->storage->getGridFs()->findOne(array(
            'filename' => $this->storage->getFilename($this->file)       
         ));
         
         $this->assertInstanceOf('\\MongoGridFSFile', $file);         
         
         $retrieved = $this->storage->retrieve($this->file);
         $this->assertInstanceof('\Xi\Filelib\File\FileObject', $retrieved);
         $this->assertFileEquals($this->fileResource, $retrieved->getRealPath());
         
         $this->storage->delete($this->file);
         
         $file = $this->storage->getGridFs()->findOne(array(
            'filename' => $this->storage->getFilename($this->file)       
         ));

         $this->assertNull($file);
         
    }
    
    /**
     * @test
     */
    public function destructorShouldDeleteRetrievedFile()
    {
        $this->storage->setFilelib($this->getFilelib()); 
        
        $this->storage->store($this->file, $this->fileResource);
         
        $file = $this->storage->getGridFs()->findOne(array(
            'filename' => $this->storage->getFilename($this->file)       
        ));
         
        $this->assertInstanceOf('\\MongoGridFSFile', $file);         
         
        $retrieved = $this->storage->retrieve($this->file);
                        
        $realPath = $retrieved->getPathname();
        
        $this->assertFileExists($realPath);
        
        unset($this->storage);       
        
        $this->assertFileNotExists($realPath);
        
    }
    
    

    /**
     * @test
     */
    public function storeAndRetrieveAndDeleteVersionShouldWorkInHarmony()
    {
        
        
        $this->storage->setFilelib($this->getFilelib()); 
        
        $this->storage->storeVersion($this->file, $this->versionProvider, $this->fileResource);
         
         $file = $this->storage->getGridFs()->findOne(array(
            'filename' => $this->storage->getFilenameVersion($this->file, $this->versionProvider)       
         ));
         
         $this->assertInstanceOf('\\MongoGridFSFile', $file);         
         
         $retrieved = $this->storage->retrieveVersion($this->file, $this->versionProvider);
         $this->assertInstanceof('\Xi\Filelib\File\FileObject', $retrieved);
         
         $this->assertFileExists($retrieved->getRealPath());
         
         $this->storage->deleteVersion($this->file, $this->versionProvider);
         
         $file = $this->storage->getGridFs()->findOne(array(
            'filename' => $this->storage->getFilenameVersion($this->file, $this->versionProvider)       
         ));

         $this->assertNull($file);
         
    }
    
    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException 
     */
    public function retrievingUnexistingFileShouldThrowException()
    {
        $file = FileItem::create(array('id' => 'lussenhofer.lus'));
        
        $this->storage->retrieve($file);
                
    }
    
    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException 
     */
    public function retrievingUnexistingFileVersionShouldThrowException()
    {
        $file = FileItem::create(array('id' => 'lussenhofer.lus'));
        $this->storage->retrieveVersion($file, $this->versionProvider);
    }
    
    
}
 