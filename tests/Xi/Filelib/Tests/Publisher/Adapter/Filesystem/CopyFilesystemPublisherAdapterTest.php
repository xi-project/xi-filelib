<?php

namespace Xi\Filelib\Tests\Publisher\Adapter\Filesystem;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Publisher\Adapter\Filesystem\CopyFilesystemPublisherAdapter;
use Xi\Filelib\Publisher\Linker\UniversalSequentialLinker;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Storage\Adapter\FilesystemStorageAdapter;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Versionable\Version;

class CopyFilesystemPublisherAdapterTest extends TestCase
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
        $publisher = new CopyFilesystemPublisherAdapter(
            ROOT_TESTS . '/data/publisher/public',
            "600",
            "700",
            ''
        );
    }

    /**
     * @test
     *
     */
    public function publishes() {

        $adapter = new CopyFilesystemPublisherAdapter(
            ROOT_TESTS . '/data/publisher/public',
            "600",
            "700",
            ''
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
        $this->assertFalse(is_link($path));

    }

    /**
     * @test
     */
    public function unpublishShouldUnpublishFileVersion()
    {
        $adapter = new CopyFilesystemPublisherAdapter(
            ROOT_TESTS . '/data/publisher/public',
            "600",
            "700",
            ''
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
        $this->assertFalse(is_link($path));

        $publisher->unpublishVersion($file, Version::get('original'));
        $this->assertFileNotExists($path);
    }


}
