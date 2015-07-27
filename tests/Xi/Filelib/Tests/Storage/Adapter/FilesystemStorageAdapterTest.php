<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage\Adapter;

use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\Adapter\FilesystemStorageAdapter;
use Xi\Filelib\Version;

/**
 * @group storage
 */
class FilesystemStorageAdapterTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        chmod(ROOT_TESTS . '/data/files', 0775);
    }

    public function tearDown()
    {
        parent::tearDown();
        chmod(ROOT_TESTS . '/data/files', 0775);
    }

    protected function getStorage()
    {
        $storage = new FilesystemStorageAdapter(ROOT_TESTS . '/data/files');
        return array($storage, false);
    }

    /**
     * @test
     */
    public function defaultsShouldProvideSaneStorage()
    {
        $root = ROOT_TESTS . '/data/files';

        $storage = new FilesystemStorageAdapter($root);
        $this->assertSame(0700, $storage->getDirectoryPermission());
        $this->assertSame(0600, $storage->getFilePermission());
        $this->assertEquals($root, $storage->getRoot());
    }

    /**
     * @test
     */
    public function rootMustBeWritableToInstantiate()
    {
        $root = ROOT_TESTS . '/data/illusive_directory';

        $this->setExpectedException('Xi\Filelib\Storage\FileIOException');
        new FilesystemStorageAdapter($root);
    }

    /**
     * @test
     */
    public function storeFailsIfDirectoryNotCreatable()
    {
        $root = ROOT_TESTS . '/data/files';
        $storage = new FilesystemStorageAdapter($root);

        chmod($root, 0400);

        $resource = Resource::create(['id' => 666, 'uuid' => Uuid::uuid4()]);

        $this->setExpectedException('Xi\Filelib\Storage\FileIOException');
        $storage->store(
            $resource,
            $this->getSelfLussingManatee()
        );
    }

    /**
     * @test
     */
    public function storeVersionFailsIfDirectoryNotCreatable()
    {
        $root = ROOT_TESTS . '/data/files';
        $storage = new FilesystemStorageAdapter($root);

        chmod($root, 0400);

        $resource = Resource::create(['id' => 666, 'uuid' => Uuid::uuid4()]);

        $this->setExpectedException('Xi\Filelib\Storage\FileIOException');
        $storage->storeVersion(
            $resource,
            Version::get('puupster'),
            $this->getSelfLussingManatee()
        );
    }
}
