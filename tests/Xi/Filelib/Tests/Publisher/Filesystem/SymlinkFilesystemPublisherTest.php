<?php

namespace Xi\Filelib\Tests\Publisher\Filesystem;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher;

class SymlinkFilesystemPublisherTest extends TestCase
{
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $publisher = new SymlinkFilesystemPublisher($this->storage, $this->fileOperator, array());

        $this->assertNull($publisher->getRelativePathToRoot());
        $relativePath = '../private';
        $this->assertSame($publisher, $publisher->setRelativePathToRoot($relativePath));
        $this->assertEquals($relativePath, $publisher->getRelativePathToRoot());
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function getRelativePathToShouldFailWhenRelativePathToRootIsMissing()
    {
        $publisher = new SymlinkFilesystemPublisher($this->storage, $this->fileOperator, array());
        $file = File::create(array('id' => 1));
        $relativePath = $publisher->getRelativePathTo($file);
    }

    /**
     * @test
     * @expectedException Xi\Filelib\FilelibException
     */
    public function getRelativePathToVersionShouldFailWhenRelativePathToRootIsMissing()
    {
        $publisher = new SymlinkFilesystemPublisher($this->storage, $this->fileOperator, array());
        $file = File::create(array('id' => 1));
        $relativePath = $publisher->getRelativePathToVersion($file, $this->versionProvider, 'tussi');
    }

    public function provideDataForRelativePathTest()
    {
        return array(

            array(
                File::create(array('id' => 1, 'resource' => Resource::create(array('id' => 1)))),
                0,
                false,
            ),
            array(
                File::create(array('id' => 2, 'resource' => Resource::create(array('id' => 2)))),
                3,
                true,
            ),
            array(
                File::create(array('id' => 3, 'resource' => Resource::create(array('id' => 3)))),
                2,
                true,
            ),
            array(
                File::create(array('id' => 4, 'resource' => Resource::create(array('id' => 4)))),
                1,
                false
            ),
        );
    }


    /**
     * @test
     * @dataProvider provideDataForRelativePathTest
     */
    public function getRelativePathToShouldReturnRelativePathToFile($file, $levelsDown, $expectedRelativePath)
    {
        $storage = $this->getMock('Xi\Filelib\Storage\FilesystemStorage');
        $storage
            ->expects($this->any())
            ->method('getRoot')
            ->will($this->returnValue('/tussin/lussu'));

        $storage
            ->expects($this->once())->method('retrieve')
            ->with($file->getResource())
            ->will($this->returnValue('/tussin/lussu/lussutustiedosto'));

        $publisher = new SymlinkFilesystemPublisher($storage, $this->fileOperator, array());
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        $publisher->setRelativePathToRoot('../private');

        $expectedPath = str_repeat("../", $levelsDown) . $publisher->getRelativePathToRoot() . '/lussutustiedosto';

        $this->assertEquals($expectedPath, $publisher->getRelativePathTo($file, $levelsDown));
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
        $storage = $this->getMock('Xi\Filelib\Storage\FilesystemStorage');
        $storage->expects($this->any())->method('getRoot')->will($this->returnValue('/tussin/lussu'));

        $this->versionProvider
            ->expects($this->atLeastOnce())
            ->method('areSharedVersionsAllowed')
            ->will($this->returnValue($versionProviderAllowsSharedResources));

        $publisher = new SymlinkFilesystemPublisher($storage, $this->fileOperator, array());
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        $publisher->setRelativePathToRoot('../private');

        if ($versionProviderAllowsSharedResources) {
            $storage
                ->expects($this->once())->method('retrieveVersion')
                ->with($file->getResource(), 'tussi')
                ->will($this->returnValue('/tussin/lussu/lussutustiedosto'));
        } else {
            $storage
                ->expects($this->once())->method('retrieveVersion')
                ->with($file->getResource(), 'tussi', $file)
                ->will($this->returnValue('/tussin/lussu/lussutustiedosto'));
        }

        $ret = $publisher->getRelativePathToVersion($file, $this->versionProvider, 'tussi', $levelsDown);

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
            $file = $this->getMockBuilder('Xi\Filelib\File\File')->getMock();

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
    public function publishShouldPublishFileWithoutRelativePaths(
        $file,
        $expectedPath,
        $expectedVersionPath,
        $expectedRealPath
    ) {
        $self = $this;
        $this->storage
            ->expects($this->atLeastOnce())
            ->method('retrieve')
            ->will(
                $this->returnCallback(
                    function (Resource $resource) use ($self) {
                        return $self->resourcePaths[$resource->getId()];
                    }
                )
            );

        $publisher = $this->getMockedPublisher();
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        $publisher->publish($file);

        $sfi = new \SplFileInfo($expectedPath);
        $this->assertTrue($sfi->isLink(), "File '{$expectedPath}' is not a symbolic link");
        $this->assertTrue($sfi->isReadable(), "File '{$expectedPath}' is not a readable symbolic link");
        $this->assertEquals($expectedRealPath, $sfi->getRealPath(), "File '{$expectedPath}' points to wrong file");
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
            ->method('areSharedVersionsAllowed')
            ->will($this->returnValue($allowSharedVersions));

        if ($allowSharedVersions) {
            $this->storage
                ->expects($this->once())->method('retrieveVersion')
                ->with($file->getResource(), 'xooxer')
                ->will($this->returnValue($this->resourcePaths[$file->getResource()->getId()]));
        } else {
            $this->storage
                ->expects($this->once())->method('retrieveVersion')
                ->with($file->getResource(), 'xooxer', $file)
                ->will($this->returnValue($this->resourcePaths[$file->getResource()->getId()]));
        }

        $publisher = $this->getMockedPublisher();
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');

        $publisher->publishVersion($file, 'xooxer', $this->versionProvider);

        $sfi = new \SplFileInfo($expectedVersionPath);
        $this->assertTrue($sfi->isLink(), "File '{$expectedVersionPath}' is not a symbolic link");
        $this->assertTrue($sfi->isReadable(), "File '{$expectedVersionPath}' is not a readable symbolic link");
        $this->assertEquals($expectedRealPath, $sfi->getRealPath(), "File '{$expectedPath}' points to wrong file");
    }


    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function publishShouldPublishFileWithRelativePaths(
        $file,
        $expectedPath,
        $expectedVersionPath,
        $expectedRealPath,
        $expectedRelativePath,
        $allowSharedVersions
    ) {
        $self = $this;
        $this->storage
            ->expects($this->atLeastOnce())
            ->method('retrieve')
            ->will(
                $this->returnCallback(
                    function (Resource $resource) use ($self) {
                        return $self->resourcePaths[$resource->getId()];
                    }
                )
            );

        $publisher = $this->getMockedPublisher();
        $publisher->setRelativePathToRoot('../private');
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        $publisher->publish($file);

        $sfi = new \SplFileInfo($expectedPath);
        $this->assertTrue($sfi->isLink(), "File '{$expectedPath}' is not a symbolic link");
        $this->assertTrue($sfi->isReadable(), "File '{$expectedPath}' is not a readable symbolic link");
        $this->assertEquals($expectedRealPath, $sfi->getRealPath(), "File '{$expectedPath}' points to wrong file");
        $this->assertEquals(
            $expectedRelativePath,
            $sfi->getLinkTarget(),
            "Relative path '{$expectedRelativePath}' points to wrong place"
        );
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
            ->method('areSharedVersionsAllowed')
            ->will($this->returnValue($allowSharedVersions));

        if ($allowSharedVersions) {
            $this->storage
                ->expects($this->once())->method('retrieveVersion')
                ->with($file->getResource(), 'xooxer')
                ->will($this->returnValue($this->resourcePaths[$file->getResource()->getId()]));
        } else {
            $this->storage
                ->expects($this->once())->method('retrieveVersion')
                ->with($file->getResource(), 'xooxer', $file)
                ->will($this->returnValue($this->resourcePaths[$file->getResource()->getId()]));
        }

        $publisher = $this->getMockedPublisher();
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        $publisher->setRelativePathToRoot('../private');

        $publisher->publishVersion($file, $this->versionProvider->getIdentifier(), $this->versionProvider);

        $sfi = new \SplFileInfo($expectedVersionPath);
        $this->assertTrue($sfi->isLink(), "File '{$expectedVersionPath}' is not a symbolic link");
        $this->assertTrue($sfi->isReadable(), "File '{$expectedVersionPath}' is not a readable symbolic link");
        $this->assertEquals($expectedRealPath, $sfi->getRealPath(), "File '{$expectedPath}' points to wrong file");
        $this->assertEquals(
            $expectedRelativePath,
            $sfi->getLinkTarget(),
            "Relative path '{$expectedRelativePath}' points to wrong place"
        );
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
    public function unpublishShouldUnpublishFile(
        $file,
        $expectedPath,
        $expectedVersionPath,
        $expectedRealPath,
        $expectedRelativePath
    ) {
        $this->createLink($expectedRealPath, $expectedPath);
        $this->assertFileExists($expectedPath);

        $publisher = $this->getMockedPublisher();
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        $publisher->unpublish($file);

        $this->assertFileNotExists($expectedPath);
    }


    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function unpublishVersionShouldUnpublishFileVersion(
        $file,
        $expectedPath,
        $expectedVersionPath,
        $expectedRealPath,
        $expectedRelativePath
    ) {
        $this->createLink($expectedRealPath, $expectedVersionPath);
        $this->assertFileExists($expectedVersionPath);

        $publisher = $this->getMockedPublisher();
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        $publisher->unpublishVersion($file, $this->versionProvider->getIdentifier(), $this->versionProvider);

        $this->assertFileNotExists($expectedVersionPath);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedPublisher()
    {
        $publisher = $this
            ->getMockBuilder('Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher')
            ->setMethods(array('getLinkerForFile'))
            ->setConstructorArgs(array($this->storage, $this->fileOperator, array()))
            ->getMock();

        $publisher
            ->expects($this->atLeastOnce())->method('getLinkerForFile')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue($this->plinker));

        return $publisher;
    }
}
