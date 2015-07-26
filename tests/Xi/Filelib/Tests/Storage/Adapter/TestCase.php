<?php

namespace Xi\Filelib\Tests\Storage\Adapter;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\Adapter\BaseTemporaryRetrievingStorageAdapter;
use Xi\Filelib\Storage\Adapter\StorageAdapter;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use DateTime;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;
use Xi\Filelib\Version;

abstract class TestCase extends \Xi\Filelib\Tests\TestCase
{
    protected $resourcePath;

    protected $resourceVersionPath;

    protected $fileSpecificVersionPath;

    protected $resource;

    protected $file;

    protected $version;

    /**
     * @var StorageAdapter
     */
    protected $storage;

    /**
     * @var bool
     */
    protected $retrievesTemporarily;

    protected $filelib;

    /**
     * @abstract
     * @return StorageAdapter
     */
    abstract protected function getStorage();

    public function setUp()
    {
        $this->resource = Resource::create(array('id' => 1, 'date_created' => new DateTime()));

        $this->file = File::create(
            array(
                'id' => 666,
                'date_created' => new DateTime(),
                'resource' => $this->resource,
            )
        );

        $this->resourcePath = $this->getSelfLussingManatee();
        $this->resourceVersionPath = $this->getSelfLussingManatee();
        $this->fileSpecificVersionPath = $this->getSelfLussingManatee();

        $this->version = Version::get('xoo');

        list ($this->storage, $this->retrievesTemporarily) = $this->getStorage();

        $this->filelib = $this->getMockedFilelib();

    }

    protected function tearDown()
    {
        $deletor = new RecursiveDirectoryDeletor('files');
        $deletor->delete();
    }

    /**
     * @test
     */
    public function storeAndRetrieveAndDeleteShouldWorkInHarmony()
    {
        $this->storage->attachTo($this->filelib);

        $this->assertFalse($this->storage->exists($this->resource));
        $ret = $this->storage->store($this->resource, $this->resourcePath);
        $this->assertInstanceOf('Xi\Filelib\Storage\Retrieved', $ret);

        $this->assertTrue($this->storage->exists($this->resource), 'Storage did not store');

        $retrieved = $this->storage->retrieve($this->resource);
        $this->assertInstanceOf('Xi\Filelib\Storage\Retrieved', $retrieved);
        $this->assertFileEquals($this->resourcePath, $retrieved->getPath());

        $this->storage->delete($this->resource);
        $this->assertFalse($this->storage->exists($this->resource));

    }

    /**
     * @test
     */
    public function versionStoreAndRetrieveAndDeleteShouldWorkInHarmony()
    {
        $this->storage->attachTo($this->filelib);

        $this->assertFalse($this->storage->versionExists($this->resource, $this->version));
        $this->assertFalse($this->storage->versionExists($this->resource, $this->version, $this->file));

        $ret1 = $this->storage->storeVersion($this->resource, $this->version, $this->resourceVersionPath);
        $this->assertTrue($this->storage->versionExists($this->resource, $this->version));
        $this->assertFalse($this->storage->versionExists($this->file, $this->version));

        $ret2 = $this->storage->storeVersion($this->file, $this->version, $this->fileSpecificVersionPath);
        $this->assertInstanceOf('Xi\Filelib\Storage\Retrieved', $ret2);

        $this->assertTrue($this->storage->versionExists($this->resource, $this->version));
        $this->assertTrue($this->storage->versionExists($this->file, $this->version));

        $retrieved = $this->storage->retrieveVersion($this->resource, $this->version);
        $retrieved2 = $this->storage->retrieveVersion($this->file, $this->version);

        $this->assertFileEquals($this->resourceVersionPath, $retrieved->getPath());
        $this->assertFileEquals($this->fileSpecificVersionPath, $retrieved2->getPath());

        $this->storage->deleteVersion($this->resource, $this->version);
        $this->assertFalse($this->storage->versionExists($this->resource, $this->version));
        $this->assertTrue($this->storage->versionExists($this->file, $this->version));

        $this->storage->deleteVersion($this->file, $this->version);
        $this->assertFalse($this->storage->versionExists($this->resource, $this->version));
        $this->assertFalse($this->storage->versionExists($this->file, $this->version));

    }

    /**
     * @test
     */
    public function overwrites()
    {
        $tussi = $this->getTussi();
        $lussi = $this->getLussi();

        $this->storage->attachTo($this->filelib);
        $this->assertFalse($this->storage->exists($this->resource));
        $ret = $this->storage->store($this->resource, $tussi);
        $this->assertTrue($this->storage->exists($this->resource));
        $retrieved = $this->storage->retrieve($this->resource);
        $this->assertFileEquals($retrieved->getPath(), $tussi);
        $ret2 = $this->storage->store($this->resource, $lussi);
        $this->assertTrue($this->storage->exists($this->resource));
        $retrieved2 = $this->storage->retrieve($this->resource);
        $this->assertFileEquals($lussi, $retrieved2->getPath());
    }

    /**
     * @test
     */
    public function overwritesVersions()
    {
        $tussi = $this->getTussi();
        $lussi = $this->getLussi();

        $version = Version::get('xooxoo');

        $this->storage->attachTo($this->filelib);
        $this->assertFalse($this->storage->versionExists($this->resource, $version));
        $ret1 = $this->storage->storeVersion($this->resource, $version, $tussi);
        $this->assertTrue($this->storage->versionExists($this->resource, $version));
        $retrieved = $this->storage->retrieveVersion($this->resource, $version);
        $this->assertFileEquals($retrieved->getPath(), $tussi);
        $ret2 = $this->storage->storeVersion($this->resource, $version, $lussi);
        $this->assertTrue($this->storage->versionExists($this->resource, $version));
        $retrieved2 = $this->storage->retrieveVersion($this->resource, $version);
        $this->assertFileEquals($lussi, $retrieved2->getPath());
    }


}
