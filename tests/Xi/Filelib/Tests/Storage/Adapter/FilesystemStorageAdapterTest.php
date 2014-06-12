<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage\Adapter;

use Xi\Filelib\LogicException;
use Xi\Filelib\Storage\Adapter\FilesystemStorageAdapter;
use Xi\Filelib\Storage\FileIOException;

/**
 * @group storage
 */
class FilesystemStorageAdapterTest extends TestCase
{
    protected function getStorage()
    {
        $dc = $this->getMock('\Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');

        $dc->expects($this->any())
             ->method('calculateDirectoryId')
             ->will($this->returnValue('1'));

        $storage = new FilesystemStorageAdapter(ROOT_TESTS . '/data/files', $dc);

        return $storage;
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
        $this->assertInstanceOf(
            'Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator',
            $storage->getDirectoryIdCalculator()
        );
        $this->assertEquals($root, $storage->getRoot());
    }

    /**
     * @test
     * @expectedException LogicException
     */
    public function rootMustBeWritableToInstantiate()
    {
        $root = ROOT_TESTS . '/data/illusive_directory';
        $storage = new FilesystemStorageAdapter($root);
    }
}
