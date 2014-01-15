<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage;

use Xi\Filelib\Storage\FilesystemStorage;
use Xi\Filelib\Exception\FileIOException;

/**
 * @group storage
 */
class FilesystemStorageTest extends TestCase
{
    protected function getStorage()
    {
        $dc = $this->getMock('\Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');

        $dc->expects($this->any())
             ->method('calculateDirectoryId')
             ->will($this->returnValue('1'));

        $storage = new FilesystemStorage(ROOT_TESTS . '/data/files', $dc);

        return $storage;
    }

    /**
     * @test
     */
    public function defaultsShouldProvideSaneStorage()
    {
        $root = ROOT_TESTS . '/data/files';

        $storage = new FilesystemStorage($root);
        $this->assertSame(0700, $storage->getDirectoryPermission());
        $this->assertSame(0600, $storage->getFilePermission());
        $this->assertInstanceOf(
            'Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator',
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
        $storage = new FilesystemStorage($root);
    }
}
