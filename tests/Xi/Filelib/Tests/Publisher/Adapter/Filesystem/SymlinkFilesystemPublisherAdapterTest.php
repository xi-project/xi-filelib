<?php

namespace Xi\Filelib\Tests\Publisher\Adapter\Filesystem;

use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Publisher\Linker\ReversibleSequentialLinker;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Publisher\Adapter\Filesystem\SymlinkFilesystemPublisherAdapter;
use Xi\Filelib\Storage\Adapter\FilesystemStorageAdapter;
use Xi\Filelib\Tests\Backend\Adapter\MemoryBackendAdapter;
use Xi\Filelib\Tests\RecursiveDirectoryDeletor;
use Xi\Filelib\Tests\Storage\Adapter\MemoryStorageAdapter;
use Xi\Filelib\Tests\TestCase;

class SymlinkFilesystemPublisherAdapterTest extends TestCase
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    private $publisher;

    public function setUp()
    {
        $this->filelib = new FileLibrary(
            new FilesystemStorageAdapter(ROOT_TESTS . '/data/files'),
            new MemoryBackendAdapter()
        );
    }

    public function tearDown()
    {
        $deletor = new RecursiveDirectoryDeletor('files');
        $deletor->delete();
    }


    /**
     * @test
     * @group persu
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
     * @group persu
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
     * @group persu
     */
    public function getRelativePathToVersionShouldFailWhenRelativePathToRootIsMissing()
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
            new ReversibleSequentialLinker()
        );
        $publisher->attachTo($this->filelib);

        $file = $this->filelib->uploadFile(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $publisher->getRelativePathToVersion($file, $this->version, $this->versionProvider, 'tussi');
    }

    /**
     * @test
     * @dataProvider provideDataForRelativePathTest
     */
    public function getRelativePathToVersionShouldReturnRelativePathToFile(
        $file,
        $levelsDown,
        $versionProviderAllowsSharedResources
    ) {

        $adapter = $this->getMockedStorageAdapter();
        $adapter->expects($this->any())->method('getRoot')->will($this->returnValue('/tussin/lussu'));
        $storage = $this->getMockedStorage($adapter);

        $filelib = $this->getMockedFilelib(null, null, null, $storage);


        $this->versionProvider
            ->expects($this->atLeastOnce())
            ->method('getApplicableVersionable')
            ->will($this->returnValue($versionProviderAllowsSharedResources ? $file->getResource() : $file));

        $publisher = new SymlinkFilesystemPublisherAdapter(
            ROOT_TESTS . '/data/publisher/public',
            "600",
            "700",
            '',
            '../private'
        );
        $publisher->attachTo($filelib);

        if ($versionProviderAllowsSharedResources) {
            $storage
                ->expects($this->once())->method('retrieveVersion')
                ->with($file->getResource(), $this->version)
                ->will($this->returnValue('/tussin/lussu/lussutustiedosto'));
        } else {
            $storage
                ->expects($this->once())->method('retrieveVersion')
                ->with($file, $this->version)
                ->will($this->returnValue('/tussin/lussu/lussutustiedosto'));
        }

        $ret = $publisher->getRelativePathToVersion($file, $this->version, $this->versionProvider, $levelsDown);

        $expectedPath = str_repeat("../", $levelsDown) . $publisher->getRelativePathToRoot() . '/lussutustiedosto';
        $this->assertEquals($expectedPath, $ret);
    }

    /**
     * @return array
     */
    public function provideDataForPublishingTests()
    {
        $files = array();

        for ($x = 1; $x <= 5; $x++) {
            $file = $this->getMockedFile();

            $file
                ->expects($this->any())
                ->method('getProfile')
                ->will($this->returnValue('profile'));

            $file
                ->expects($this->any())
                ->method('getResource')
                ->will($this->returnValue(Resource::create(array('id' => $x))));

            $file
                ->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($x));

            $files[$x-1] = $file;
        }

        $ret = array(
            array(
                $files[0],
                ROOT_TESTS . '/data/publisher/public/lussin/tussin/1.lus',
                ROOT_TESTS . '/data/publisher/public/lussin/tussin/1-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/1/1',
                '../../../private/1/1',
                true,
            ),
            array(
                $files[1],
                ROOT_TESTS . '/data/publisher/public/lussin/tussin/jussin/pussin/2.lus',
                ROOT_TESTS . '/data/publisher/public/lussin/tussin/jussin/pussin/2-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/2/2/2',
                '../../../../../private/2/2/2',
                false,
            ),
            array(
                $files[2],
                ROOT_TESTS . '/data/publisher/public/tohtori/vesalan/suuri/otsa/3.lus',
                ROOT_TESTS . '/data/publisher/public/tohtori/vesalan/suuri/otsa/3-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/3/3/3/3',
                '../../../../../private/3/3/3/3',
                false,
            ),
            array(
                $files[3],
                ROOT_TESTS . '/data/publisher/public/lussen/hof/4.lus',
                ROOT_TESTS . '/data/publisher/public/lussen/hof/4-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/666/4',
                '../../../private/666/4',
                true
            ),
            array(
                $files[4],
                ROOT_TESTS . '/data/publisher/public/5.lus',
                ROOT_TESTS . '/data/publisher/public/5-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/1/5',
                '../private/1/5',
                true,
            ),

        );

        return $ret;

    }

    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function publishShouldPublishFileVersionWithoutRelativePaths(
        $file,
        $expectedPath,
        $expectedVersionPath,
        $expectedRealPath,
        $expectedRelativePath,
        $allowSharedVersions
    ) {
        $this->versionProvider
            ->expects($this->atLeastOnce())
            ->method('getApplicableVersionable')
            ->will($this->returnValue($allowSharedVersions ? $file->getResource() : $file));

        if ($allowSharedVersions) {
            $this->storage
                ->expects($this->once())->method('retrieveVersion')
                ->with($file->getResource(), $this->version)
                ->will($this->returnValue($this->resourcePaths[$file->getResource()->getId()]));
        } else {
            $this->storage
                ->expects($this->once())->method('retrieveVersion')
                ->with($file, $this->version)
                ->will($this->returnValue($this->resourcePaths[$file->getResource()->getId()]));
        }

        $publisher = new SymlinkFilesystemPublisherAdapter(
            ROOT_TESTS . '/data/publisher/public',
            "600",
            "700",
            ''
        );
        $publisher->attachTo($this->filelib);

        $publisher->publish($file, $this->version, $this->versionProvider, $this->plinker);

        $sfi = new \SplFileInfo($expectedVersionPath);
        $this->assertTrue($sfi->isLink(), "File '{$expectedVersionPath}' is not a symbolic link");
        $this->assertTrue($sfi->isReadable(), "File '{$expectedVersionPath}' is not a readable symbolic link");
    }

    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function publishShouldPublishFileVersionWithRelativePaths(
        $file,
        $expectedPath,
        $expectedVersionPath,
        $expectedRealPath,
        $expectedRelativePath,
        $allowSharedVersions
    ) {
        $this->versionProvider
            ->expects($this->atLeastOnce())
            ->method('getApplicableVersionable')
            ->will($this->returnValue($allowSharedVersions ? $file->getResource() : $file));

        if ($allowSharedVersions) {
            $this->storage
                ->expects($this->once())->method('retrieveVersion')
                ->with($file->getResource(), $this->version)
                ->will($this->returnValue($this->resourcePaths[$file->getResource()->getId()]));
        } else {
            $this->storage
                ->expects($this->once())->method('retrieveVersion')
                ->with($file, $this->version)
                ->will($this->returnValue($this->resourcePaths[$file->getResource()->getId()]));
        }

        $publisher = new SymlinkFilesystemPublisherAdapter(
            ROOT_TESTS . '/data/publisher/public',
            "600",
            "700",
            '',
            '../private'
        );
        $publisher->attachTo($this->filelib);

        $publisher->publish($file, $this->version, $this->versionProvider, $this->plinker);

        $sfi = new \SplFileInfo($expectedVersionPath);
        $this->assertTrue($sfi->isLink(), "File '{$expectedVersionPath}' is not a symbolic link");
        $this->assertTrue($sfi->isReadable(), "File '{$expectedVersionPath}' is not a readable symbolic link");
    }

    private function createLink($target, $link)
    {
        if (!is_dir(dirname($link))) {
            mkdir(dirname($link), 0700, true);
        }
        symlink($target, $link);
    }

    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function unpublishShouldUnpublishFileVersion(
        $file,
        $expectedPath,
        $expectedVersionPath,
        $expectedRealPath,
        $expectedRelativePath
    ) {
        $this->createLink($expectedRealPath, $expectedVersionPath);
        $this->assertFileExists($expectedVersionPath);

        $publisher = new SymlinkFilesystemPublisherAdapter(
            ROOT_TESTS . '/data/publisher/public',
            "600",
            "700",
            '',
            '../private'
        );
        $publisher->attachTo($this->filelib);

        $publisher->unpublish($file, $this->version, $this->versionProvider, $this->plinker);

        $this->assertFileNotExists($expectedVersionPath);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockPublisher()
    {
        $publisher = $this
            ->getMockBuilder('Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher')
            ->setMethods(array('getLinkerForFile'))
            ->setConstructorArgs(array($this->storage, $this->fileRepository, array()))
            ->getMock();

        $publisher
            ->expects($this->atLeastOnce())->method('getLinkerForFile')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue($this->plinker));

        $publisher->attachTo($this->filelib);

        return $publisher;
    }

}
