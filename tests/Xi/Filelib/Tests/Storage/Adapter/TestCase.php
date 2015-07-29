<?php

namespace Xi\Filelib\Tests\Storage\Adapter;

use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\Adapter\BaseTemporaryRetrievingStorageAdapter;
use Xi\Filelib\Storage\Adapter\StorageAdapter;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use DateTime;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;
use Xi\Filelib\Versionable\Version;

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
        $this->resource = Resource::create([
            'id' => 1,
            'date_created' => new DateTime(),
            'uuid' => Uuid::uuid4(),
        ]);

        $this->file = File::create(
            array(
                'id' => 666,
                'uuid' => Uuid::uuid4(),
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
}
