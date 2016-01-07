<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Plugin\ListFiles;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use Pekkis\DirectoryCalculator\DirectoryCalculator;
use Pekkis\DirectoryCalculator\Strategy\TimeStrategy;
use Prophecy\Argument;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\FileIOException;
use Xi\Filelib\Tool\PathCalculator\ImprovedPathCalculator;
use Xi\Filelib\Storage\Adapter\FlysystemStorageAdapter;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;
use Xi\Filelib\Version;

/**
 * @group storage
 */
class FlysystemStorageAdapterTest extends TestCase
{
    private $filesystem;

    /**
     * @return Filesystem
     */
    private function getFilesystem()
    {
        if (!$this->filesystem) {
            $adapter = new LocalAdapter(ROOT_TESTS . '/data/files');
            $this->filesystem = new Filesystem($adapter);
        }

        return $this->filesystem;
    }

    protected function tearDown()
    {
        $deletor = new RecursiveDirectoryDeletor('files');
        $deletor->delete();
    }

    protected function getStorage()
    {
        $filesystem = $this->getFilesystem();
        $filesystem->addPlugin(new ListFiles());

        $dc = new DirectoryCalculator(new TimeStrategy());
        $pc = new ImprovedPathCalculator($dc);
        $storage = new FlysystemStorageAdapter($filesystem, $pc, false);
        return array($storage, true);
    }

    /**
     * @test
     */
    public function retrieveFailThrows()
    {
        $filesystem = $this->prophesize(Filesystem::class);
        $adapter = new FlysystemStorageAdapter($filesystem->reveal());

        $resource = Resource::create([
            'id' => 'xooxoo',
            'uuid' => '123e4567-e89b-12d3-a456-426655440000'
        ]);

        $filesystem->get(Argument::type('string'))->shouldBeCalled()->willReturn(false);

        $this->setExpectedException(FileIOException::class);

        $adapter->retrieve($resource);
    }

    /**
     * @test
     */
    public function retrieveVersionFailThrows()
    {
        $filesystem = $this->prophesize(Filesystem::class);
        $adapter = new FlysystemStorageAdapter($filesystem->reveal());

        $resource = Resource::create([
            'id' => 'xooxoo',
            'uuid' => '123e4567-e89b-12d3-a456-426655440000'
        ]);

        $filesystem->get(Argument::type('string'), Argument::any())->shouldBeCalled()->willReturn(false);

        $this->setExpectedException(FileIOException::class);

        $adapter->retrieveVersion($resource, Version::get('tussi'));
    }

    /**
     * @test
     */
    public function deleteFailThrows()
    {
        $filesystem = $this->prophesize(Filesystem::class);
        $adapter = new FlysystemStorageAdapter($filesystem->reveal());

        $resource = Resource::create([
            'id' => 'xooxoo',
            'uuid' => '123e4567-e89b-12d3-a456-426655440000'
        ]);

        $filesystem->delete(Argument::type('string'))->shouldBeCalled()->willReturn(false);

        $this->setExpectedException(FileIOException::class);

        $adapter->delete($resource);
    }

    /**
     * @test
     */
    public function deleteVersionFailThrows()
    {
        $filesystem = $this->prophesize(Filesystem::class);
        $adapter = new FlysystemStorageAdapter($filesystem->reveal());

        $resource = Resource::create([
            'id' => 'xooxoo',
            'uuid' => '123e4567-e89b-12d3-a456-426655440000'
        ]);

        $filesystem->delete(Argument::type('string'), Argument::any())->shouldBeCalled()->willReturn(false);

        $this->setExpectedException(FileIOException::class);

        $adapter->deleteVersion($resource, Version::get('tussi'));
    }

    /**
     * @test
     */
    public function storeFailThrows()
    {
        $filesystem = $this->prophesize(Filesystem::class);
        $adapter = new FlysystemStorageAdapter($filesystem->reveal());

        $resource = Resource::create([
            'id' => 'xooxoo',
            'uuid' => '123e4567-e89b-12d3-a456-426655440000'
        ]);

        $tmp = $this->getTussi();

        $filesystem->put(
            Argument::type('string'),
            Argument::any(),
            [
                'visibility' => AdapterInterface::VISIBILITY_PRIVATE
            ]
        )->shouldBeCalled()->willReturn(false);

        $this->setExpectedException(FileIOException::class);

        $adapter->store($resource, $tmp);
    }

    /**
     * @test
     */
    public function storeVersionFailThrows()
    {
        $filesystem = $this->prophesize(Filesystem::class);
        $adapter = new FlysystemStorageAdapter($filesystem->reveal());

        $resource = Resource::create([
            'id' => 'xooxoo',
            'uuid' => '123e4567-e89b-12d3-a456-426655440000'
        ]);

        $tmp = $this->getTussi();

        $filesystem->put(
            Argument::type('string'),
            Argument::any(),
            [
                'visibility' => AdapterInterface::VISIBILITY_PRIVATE
            ]
        )->shouldBeCalled()->willReturn(false);

        $this->setExpectedException(FileIOException::class);

        $adapter->storeVersion($resource, Version::get('tussi'), $tmp);
    }

}
