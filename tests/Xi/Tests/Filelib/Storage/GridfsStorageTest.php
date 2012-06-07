<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Storage;

use Mongo;
use MongoDB;

use MongoConnectionException;
use Xi\Filelib\Storage\GridfsStorage;
use Xi\Filelib\File\Resource;
use Xi\Filelib\FilelibException;

/**
 * @group storage
 */
class GridFsStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MondoDB
     */
    protected $mongo;

    /**
     * @var GridfsStorage
     */
    protected $storage;

    protected $resource;

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

        $this->resource = Resource::create(array('id' => 1));

        $this->fileResource = realpath(ROOT_TESTS . '/data') . '/self-lussing-manatee.jpg';

        $this->mongo = $mongo->filelib_tests;

        $this->storage = new GridfsStorage($this->mongo, ROOT_TESTS . '/data/temp');

        $dc = $this->getMock('\Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->any())
            ->method('calculateDirectoryId')
            ->will($this->returnValue('1'));

        $this->version = 'xoo';
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
        $this->storage->store($this->resource, $this->fileResource);

         $file = $this->storage->getGridFs()->findOne(array(
            'filename' => $this->storage->getFilename($this->resource)
         ));

         $this->assertInstanceOf('\\MongoGridFSFile', $file);

         $retrieved = $this->storage->retrieve($this->resource);
         $this->assertInstanceof('\Xi\Filelib\File\FileObject', $retrieved);
         $this->assertFileEquals($this->fileResource, $retrieved->getRealPath());

         $this->storage->delete($this->resource);

         $file = $this->storage->getGridFs()->findOne(array(
            'filename' => $this->storage->getFilename($this->resource)
         ));

         $this->assertNull($file);

    }

    /**
     * @test
     */
    public function destructorShouldDeleteRetrievedFile()
    {
        $this->storage->store($this->resource, $this->fileResource);

        $file = $this->storage->getGridFs()->findOne(array(
            'filename' => $this->storage->getFilename($this->resource)
        ));

        $this->assertInstanceOf('\\MongoGridFSFile', $file);

        $retrieved = $this->storage->retrieve($this->resource);

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
        $this->storage->storeVersion($this->resource, $this->version, $this->fileResource);

         $file = $this->storage->getGridFs()->findOne(array(
            'filename' => $this->storage->getFilenameVersion($this->resource, $this->version)
         ));

         $this->assertInstanceOf('\\MongoGridFSFile', $file);

         $retrieved = $this->storage->retrieveVersion($this->resource, $this->version);
         $this->assertInstanceof('\Xi\Filelib\File\FileObject', $retrieved);

         $this->assertFileExists($retrieved->getRealPath());

         $this->storage->deleteVersion($this->resource, $this->version);

         $file = $this->storage->getGridFs()->findOne(array(
            'filename' => $this->storage->getFilenameVersion($this->resource, $this->version)
         ));

         $this->assertNull($file);

    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function retrievingUnexistingFileShouldThrowException()
    {
        $file = Resource::create(array('id' => 'lussenhofer.lus'));
        $this->storage->retrieve($file);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function retrievingUnexistingFileVersionShouldThrowException()
    {
        $file = Resource::create(array('id' => 'lussenhofer.lus'));
        $this->storage->retrieveVersion($file, $this->version);
    }
}
