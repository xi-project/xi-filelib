<?php

namespace Xi\Tests\Filelib\Storage;

use \Mongo,
    \MongoDB,
    \MongoGridFS,
    \MongoCollection,
    \Xi\Filelib\Storage\GridfsStorage
    ;

class GridFsStorageTest extends \Xi\Tests\Filelib\TestCase
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

        $this->file = \Xi\Filelib\File\FileItem::create(array('id' => 1));
        
        $this->fileResource = realpath(ROOT_TESTS . '/data') . '/self-lussing-manatee.jpg';
        
                
        $this->filelib = $this->getFilelib();
        
        // @todo: to config
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
        if (extension_loaded('mongo')) {
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
    
    
    
    
}
 