<?php

namespace Xi\Tests\Filelib\Storage;

use Xi\Filelib\Storage\Storage;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;

abstract class TestCase extends \Xi\Tests\Filelib\TestCase
{
    protected $resourcePath;

    protected $resourceVersionPath;

    protected $fileSpecificVersionPath;

    protected $resource;

    protected $file;

    protected $version;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @abstract
     * @return Storage
     */
    protected abstract function getStorage();

    public function setUp()
    {
        $this->resource = Resource::create(array('id' => 1));
        $this->file = File::create(array('id' => 666));

        $this->resourcePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $this->resourceVersionPath = ROOT_TESTS . '/data/self-lussing-manatee-mini.jpg';
        $this->fileSpecificVersionPath = ROOT_TESTS . '/data/self-lussing-manatee-file-specific.jpg';

        $this->version = 'xoo';

        $this->storage = $this->getStorage();

    }


    /**
     * @test
     */
    public function storeAndRetrieveAndDeleteShouldWorkInHarmony()
    {
        $this->assertFalse($this->storage->exists($this->resource));
        $this->storage->store($this->resource, $this->resourcePath);

        $this->assertTrue($this->storage->exists($this->resource));

        $retrieved = $this->storage->retrieve($this->resource);
        $this->assertInternalType('string', $retrieved);

        $this->assertFileEquals($this->resourcePath, $retrieved);

        $this->storage->delete($this->resource);
        $this->assertFalse($this->storage->exists($this->resource));

    }


    /**
     * @test
     */
    public function versionStoreAndRetrieveAndDeleteShouldWorkInHarmony()
    {
        $this->assertFalse($this->storage->versionExists($this->resource, $this->version));
        $this->assertFalse($this->storage->versionExists($this->resource, $this->version, $this->file));

        $this->storage->storeVersion($this->resource, $this->version, $this->resourceVersionPath);
        $this->assertTrue($this->storage->versionExists($this->resource, $this->version));
        $this->assertFalse($this->storage->versionExists($this->resource, $this->version, $this->file));

        $this->storage->storeVersion($this->resource, $this->version, $this->fileSpecificVersionPath, $this->file);
        $this->assertTrue($this->storage->versionExists($this->resource, $this->version));
        $this->assertTrue($this->storage->versionExists($this->resource, $this->version, $this->file));

        $retrieved = $this->storage->retrieveVersion($this->resource, $this->version);
        $retrieved2 = $this->storage->retrieveVersion($this->resource, $this->version, $this->file);

        $this->assertFileEquals($this->resourceVersionPath, $retrieved);
        $this->assertFileEquals($this->fileSpecificVersionPath, $retrieved2);

        $this->storage->deleteVersion($this->resource, $this->version);
        $this->assertFalse($this->storage->versionExists($this->resource, $this->version));
        $this->assertTrue($this->storage->versionExists($this->resource, $this->version, $this->file));

        $this->storage->deleteVersion($this->resource, $this->version, $this->file);
        $this->assertFalse($this->storage->versionExists($this->resource, $this->version));
        $this->assertFalse($this->storage->versionExists($this->resource, $this->version, $this->file));

    }




}
