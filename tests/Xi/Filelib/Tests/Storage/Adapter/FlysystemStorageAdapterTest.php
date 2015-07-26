<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage\Adapter;

use League\Flysystem\Plugin\ListFiles;
use Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use Xi\Filelib\Storage\Adapter\Filesystem\PathCalculator\ImprovedPathCalculator;
use Xi\Filelib\Storage\Adapter\FlysystemStorageAdapter;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;

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

        $dc = new TimeDirectoryIdCalculator();
        $pc = new ImprovedPathCalculator($dc);
        $storage = new FlysystemStorageAdapter($filesystem, $pc, false);
        return array($storage, true);
    }

}
