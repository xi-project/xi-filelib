<?php

namespace Xi\Filelib\Tests\Storage;

use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use DateTime;

abstract class TestCase extends \Xi\Filelib\Tests\TestCase
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

        $this->resourcePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $this->resourceVersionPath = ROOT_TESTS . '/data/self-lussing-manatee-mini.jpg';
        $this->fileSpecificVersionPath = ROOT_TESTS . '/data/self-lussing-manatee-file-specific.jpg';

        $this->version = 'xoo';

        $this->storage = $this->getStorage();

    }

    protected function tearDown()
    {
        $diter = new RecursiveDirectoryIterator(ROOT_TESTS . '/data/files');
        $riter = new RecursiveIteratorIterator($diter, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($riter as $item) {
            if ($item->isFile() && $item->getFilename() !== '.gitignore') {
                @unlink($item->getPathName());
            }
        }

        foreach ($riter as $item) {
            if ($item->isDir() && !in_array($item->getPathName(), array('.', '..'))) {
                @rmdir($item->getPathName());
            }
        }
    }

    /**
     * @test
     */
    public function deletingUnexistingResourceShouldThrowException()
    {
        $this->setExpectedException('Xi\Filelib\Storage\FileIOException');
        $this->storage->delete($this->resource);
    }

    /**
     * @test
     */
    public function storeAndRetrieveAndDeleteShouldWorkInHarmony()
    {
        $this->assertFalse($this->storage->exists($this->resource));
        $this->storage->store($this->resource, $this->resourcePath);

        $this->assertTrue($this->storage->exists($this->resource), 'Storage did not store');

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
        $this->assertFalse($this->storage->versionExists($this->file, $this->version));

        $this->storage->storeVersion($this->file, $this->version, $this->fileSpecificVersionPath);
        $this->assertTrue($this->storage->versionExists($this->resource, $this->version));
        $this->assertTrue($this->storage->versionExists($this->file, $this->version));

        $retrieved = $this->storage->retrieveVersion($this->resource, $this->version);
        $retrieved2 = $this->storage->retrieveVersion($this->file, $this->version);

        $this->assertFileEquals($this->resourceVersionPath, $retrieved);
        $this->assertFileEquals($this->fileSpecificVersionPath, $retrieved2);

        $this->storage->deleteVersion($this->resource, $this->version);
        $this->assertFalse($this->storage->versionExists($this->resource, $this->version));
        $this->assertTrue($this->storage->versionExists($this->file, $this->version));

        $this->storage->deleteVersion($this->file, $this->version);
        $this->assertFalse($this->storage->versionExists($this->resource, $this->version));
        $this->assertFalse($this->storage->versionExists($this->file, $this->version));

    }
}
