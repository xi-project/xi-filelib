<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage\Adapter;

use League\Flysystem\Plugin\ListFiles;
use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Resource\ConcreteResource;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use Xi\Filelib\Tool\PathCalculator\ImprovedPathCalculator;
use Xi\Filelib\Storage\Adapter\FilesystemStorageAdapter;
use Xi\Filelib\Storage\Adapter\FlysystemStorageAdapter;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;

/**
 * @group storage
 */
class TussiTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function testForEquality()
    {
        $adapter = new LocalAdapter(ROOT_TESTS . '/data/files');
        $pc = new ImprovedPathCalculator();

        $filesystem = new Filesystem($adapter);

        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter(),
            null,
            ROOT_TESTS . '/data/temp'
        );

        $storage1 = new FlysystemStorageAdapter($filesystem, $pc, false);
        $storage1->attachTo($filelib);

        $temp = $this->getSelfLussingManatee();

        $storage2 = new FilesystemStorageAdapter(
            ROOT_TESTS . '/data/files',
            $pc
        );
        $storage2->attachTo($filelib);

        $resource = ConcreteResource::create([
            'id' => 'lubber',
            'uuid' => Uuid::uuid4()
        ]);

        $ret = $storage1->store($resource, $temp);

        $this->assertFileEquals(
            $storage1->retrieve($resource)->getPath(),
            $storage2->retrieve($resource)->getPath()
        );
    }

}
