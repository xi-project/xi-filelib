<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Storage;

use Xi\Filelib\Storage\FilesystemStorage;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Xi\Filelib\Exception\FileIOException;

/**
 * @group storage
 */
class FilesystemStorageTest extends TestCase
{

    public function tearDown()
    {
        $diter = new RecursiveDirectoryIterator($this->storage->getRoot());
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

    protected function getStorage()
    {
        $dc = $this->getMock('\Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->any())
             ->method('calculateDirectoryId')
             ->will($this->returnValue('1'));

        $storage = new FilesystemStorage();
        $storage->setDirectoryIdCalculator($dc);
        $storage->setCacheDirectoryIds(false);
        $storage->setRoot(ROOT_TESTS . '/data/files');

        return $storage;

    }

    /**
     * @test
     */
    public function filePermissionGetAndSetShouldWorkAsExpected()
    {
        $this->assertEquals(0600, $this->storage->getFilePermission());
        $this->storage->setFilePermission(755);
        $this->assertEquals(0755, $this->storage->getFilePermission());
    }

    /**
     * @test
     */
    public function rootGetAndSetShouldWorkAsExpected()
    {
        $storage = new FilesystemStorage();
        $this->assertNull($storage->getRoot());
        $storage->setRoot(ROOT_TESTS . '/data');

        $this->assertEquals(ROOT_TESTS . '/data', $storage->getRoot());
    }

    /**
     * @test
     */
    public function directoryPermissionGetAndSetShouldWorkAsExpected()
    {
        $this->assertEquals(0700, $this->storage->getDirectoryPermission());
        $this->storage->setDirectoryPermission(755);
        $this->assertEquals(0755, $this->storage->getDirectoryPermission());
    }

    /**
     * @test
     */
    public function directoryCalculatorGetAndSetShouldWorkAsExpected()
    {
         $storage = new FilesystemStorage();

         $dc = $this->getMock('\Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
         $dc->expects($this->any())
             ->method('calculateDirectoryId')
             ->will($this->returnValue('1'));

         $this->assertNull($storage->getDirectoryIdCalculator());

         $storage->setDirectoryIdCalculator($dc);

         $this->assertEquals($dc, $storage->getDirectoryIdCalculator());
    }

    /**
     * @test
     */
    public function directoryIdCalculationWithoutCachingShouldCallMethodEveryTime()
    {
        $dc = $this->getMock('Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->exactly(3))
             ->method('calculateDirectoryId')
             ->will($this->returnValue('1'));

        $this->storage->setDirectoryIdCalculator($dc);

        $this->assertFalse($this->storage->getCacheDirectoryIds());

        $this->assertEquals(1, $this->storage->getDirectoryId($this->resource));
        $this->assertEquals(1, $this->storage->getDirectoryId($this->resource));
        $this->assertEquals(1, $this->storage->getDirectoryId($this->resource));

    }

    /**
     * @test
     */
    public function directoryIdCalculationWithCachingShouldCallMethodOnlyOnce()
    {
        $dc = $this->getMock('Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator');
        $dc->expects($this->exactly(1))
             ->method('calculateDirectoryId')
             ->will($this->returnValue('1'));

        $this->storage->setDirectoryIdCalculator($dc);

        $this->assertFalse($this->storage->getCacheDirectoryIds());
        $this->storage->setCacheDirectoryIds(true);
        $this->assertTrue($this->storage->getCacheDirectoryIds());

        $this->assertEquals(1, $this->storage->getDirectoryId($this->resource));
        $this->assertEquals(1, $this->storage->getDirectoryId($this->resource));
        $this->assertEquals(1, $this->storage->getDirectoryId($this->resource));

    }

    /**
     * @test
     * @expectedException Xi\Filelib\Exception\FileIOException
     */
    public function storeShouldFailIfRootIsNotDefined()
    {
        $storage = new FilesystemStorage();
        $storage->store($this->resource, $this->resourcePath);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\Exception\FileIOException
     */
    public function storeShouldFailIfRootIsNotWritable()
    {
        $storage = new FilesystemStorage();
        $storage->setRoot(ROOT_TESTS . '/data/illusive_directory');
        $storage->store($this->resource, $this->resourcePath);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\Exception\FileIOException
     */
    public function storeVersionShouldFailIfRootIsNotDefined()
    {
        $storage = new FilesystemStorage();
        $storage->storeVersion($this->resource, $this->version, $this->resourcePath);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\Exception\FileIOException
     */
    public function storeVersionShouldFailIfRootIsNotWritable()
    {
        $storage = new FilesystemStorage();
        $storage->setRoot(ROOT_TESTS . '/data/illusive_directory');
        $storage->storeVersion($this->resource, $this->version, $this->resourcePath);
    }

}
