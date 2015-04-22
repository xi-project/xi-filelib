<?php

namespace Xi\Filelib\Tests\Publisher\Adapter\Filesystem;

use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Publisher\Linker\UniversalSequentialLinker;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Publisher\Adapter\Filesystem\SymlinkFilesystemPublisherAdapter;
use Xi\Filelib\Storage\Adapter\FilesystemStorageAdapter;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;
use Xi\Filelib\Tests\Storage\Adapter\MemoryStorageAdapter;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Version;

class SymlinkFilesystemPublisherAdapterTest extends TestCase
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    public function setUp()
    {
        $this->filelib = new FileLibrary(
            new FilesystemStorageAdapter(ROOT_TESTS . '/data/publisher/private'),
            new MemoryBackendAdapter()
        );

        $this->filelib->addPlugin(new OriginalVersionPlugin(), [], 'original');
    }

    public function tearDown()
    {
        $deletor = new RecursiveDirectoryDeletor('publisher/private');
        $deletor->delete();

        $deletor = new RecursiveDirectoryDeletor('publisher/public');
        $deletor->delete();
    }


    /**
     * @test
     */
    public function shouldInitialize()
    {
        $publisher = new SymlinkFilesystemPublisherAdapter(
            ROOT_TESTS . '/data/publisher/public',
            "600",
            "700",
            '',
            null
        );
        $this->assertNull($publisher->getRelativePathToRoot());
    }

    /**
     * @test
     */
    public function attachToFailsWithNonFilesystemStorage()
    {
        $filelib = new FileLibrary(
            new MemoryStorageAdapter(),
            new MemoryBackendAdapter()
        );

        $this->setExpectedException('Xi\Filelib\InvalidArgumentException');

        $adapter = new SymlinkFilesystemPublisherAdapter(ROOT_TESTS . '/data/publisher/public');
        $adapter->attachTo($filelib);
    }


    /**
     * @test
     */
    public function publishesWithEmptyRelativePath()
    {
        $adapter = new SymlinkFilesystemPublisherAdapter(
            ROOT_TESTS . '/data/publisher/public',
            "600",
            "700",
            '',
            null
        );

        $publisher = new Publisher(
            $adapter,
            new UniversalSequentialLinker()
        );
        $publisher->attachTo($this->filelib);

        $file = $this->filelib->uploadFile(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $publisher->publishVersion($file, Version::get('original'));

        $path = ROOT_TESTS . '/data/publisher/public/' . $publisher->getUrl($file, Version::get('original'));

        $this->assertFileExists($path);
        $this->assertTrue(is_link($path));
    }

    /**
     * @test
     */
    public function publishesWithRelativePaths() {

        $adapter = new SymlinkFilesystemPublisherAdapter(
            ROOT_TESTS . '/data/publisher/public',
            "600",
            "700",
            '',
            '../private'
        );

        $publisher = new Publisher(
            $adapter,
            new UniversalSequentialLinker()
        );
        $publisher->attachTo($this->filelib);

        $file = $this->filelib->uploadFile(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $publisher->publishVersion($file, Version::get('original'));

        $path = ROOT_TESTS . '/data/publisher/public/' . $publisher->getUrl($file, Version::get('original'));

        $this->assertFileExists($path);
        $this->assertTrue(is_link($path));

    }

    /**
     * @test
     */
    public function unpublishShouldUnpublishFileVersion()
    {
        $adapter = new SymlinkFilesystemPublisherAdapter(
            ROOT_TESTS . '/data/publisher/public',
            "600",
            "700",
            '',
            '../private'
        );

        $publisher = new Publisher(
            $adapter,
            new UniversalSequentialLinker()
        );
        $publisher->attachTo($this->filelib);

        $file = $this->filelib->uploadFile(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $publisher->publishVersion($file, Version::get('original'));

        $path = ROOT_TESTS . '/data/publisher/public/' . $publisher->getUrl($file, Version::get('original'));

        $this->assertFileExists($path);
        $this->assertTrue(is_link($path));

        $publisher->unpublishVersion($file, Version::get('original'));

        $this->assertFileNotExists($path);
    }
}
