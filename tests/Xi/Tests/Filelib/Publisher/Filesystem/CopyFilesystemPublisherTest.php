<?php

namespace Xi\Tests\Filelib\Publisher\Filesystem;


use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Publisher\Filesystem\CopyFilesystemPublisher;

class CopyFilesystemPublisherTest extends TestCase
{

    protected $plinker;

    public function setUp()
    {
        parent::setUp();

        $linker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();

        $linker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();
        $linker->expects($this->any())->method('getLinkVersion')
            ->will($this->returnCallback(function($file, $version) {
            return $this->linkPaths[$file->getId()] . '/' . $file->getId() . '-' . $version . '.lus';
        }));
        $linker->expects($this->any())->method('getLink')
            ->will($this->returnCallback(function($file) {
            return $this->linkPaths[$file->getId()] . '/' . $file->getId() . '.lus';
        }));

        $this->plinker = $linker;

    }

    public function provideDataForPublishingTests()
    {
        $files = array();

        for ($x = 1; $x <= 5; $x++) {
            $file = $this->getMockBuilder('Xi\Filelib\File\File')->getMock();

            $file->expects($this->any())->method('getProfile')
                ->will($this->returnValue('profile'));

            $file->expects($this->any())->method('getResource')->will($this->returnValue(Resource::create(array('id' => $x))));

            $file->expects($this->any())->method('getId')->will($this->returnValue($x));

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
    public function publishShouldPublishFile($file, $expectedPath, $expectedVersionPath, $expectedRealPath)
    {
        $this->storage->expects($this->atLeastOnce())->method('retrieve')
            ->will($this->returnCallback(function(Resource $resource) {
            return $this->resourcePaths[$resource->getId()];
        }));

        $this->filelib->setStorage($this->storage);

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\CopyFilesystemPublisher')
                          ->setMethods(array('getLinkerForFile'))
                          ->getMock();

        $publisher->expects($this->atLeastOnce())->method('getLinkerForFile')
                  ->with($this->isInstanceOf('Xi\Filelib\File\File'))
                  ->will($this->returnValue($this->plinker));

        $publisher->setFilelib($this->filelib);
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        // $publisher->setRelativePathToRoot('../private');

        $publisher->publish($file);

        $sfi = new \SplFileInfo($expectedPath);

        $this->assertFalse($sfi->isLink(), "File '{$expectedPath}' is a symbolic link");

        $this->assertTrue($sfi->isReadable(), "File '{$expectedPath}' is not a readable symbolic link");

        $this->assertFileEquals($expectedRealPath, $sfi->getRealPath(), "File '{$expectedPath}' points to wrong file");

    }


    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function publishShouldPublishFileVersion($file, $expectedPath, $expectedVersionPath, $expectedRealPath, $expectedRelativePath, $allowSharedVersions)
    {
        $this->versionProvider->expects($this->atLeastOnce())
            ->method('areSharedVersionsAllowed')
            ->will($this->returnValue($allowSharedVersions));

        if ($allowSharedVersions) {
            $this->storage->expects($this->once())->method('retrieveVersion')
                ->with($file->getResource(), 'xooxer')
                ->will($this->returnValue($this->resourcePaths[$file->getResource()->getId()]));
        } else {
            $this->storage->expects($this->once())->method('retrieveVersion')
                ->with($file->getResource(), 'xooxer', $file)
                ->will($this->returnValue($this->resourcePaths[$file->getResource()->getId()]));
        }

        $this->filelib->setStorage($this->storage);

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\CopyFilesystemPublisher')
                          ->setMethods(array('getLinkerForFile'))
                          ->getMock();

        $publisher->expects($this->atLeastOnce())->method('getLinkerForFile')
                  ->with($this->isInstanceOf('Xi\Filelib\File\File'))
                  ->will($this->returnValue($this->plinker));

        $publisher->setFilelib($this->filelib);
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        // $publisher->setRelativePathToRoot('../private');

        $publisher->publishVersion($file, $this->versionProvider->getIdentifier(), $this->versionProvider);

        $sfi = new \SplFileInfo($expectedVersionPath);

        $this->assertFalse($sfi->isLink(), "File '{$expectedVersionPath}' is a symbolic link");

        $this->assertTrue($sfi->isReadable(), "File '{$expectedVersionPath}' is not a readable symbolic link");


        $this->assertFileEquals($expectedRealPath, $sfi->getRealPath(), "File '{$expectedPath}' points to wrong file");

    }



    private function createFile($target, $link)
    {
        if (!is_dir(dirname($link))) {
            mkdir(dirname($link), 0700, true);
        }
        copy($target, $link);
    }


    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function unpublishShouldUnpublishFile($file, $expectedPath, $expectedVersionPath, $expectedRealPath, $expectedRelativePath)
    {
        $this->createFile($expectedRealPath, $expectedPath);

        $this->assertFileExists($expectedPath);

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\CopyFilesystemPublisher')
                          ->setMethods(array('getLinkerForFile'))
                          ->getMock();

        $publisher->expects($this->atLeastOnce())->method('getLinkerForFile')
                  ->with($this->isInstanceOf('Xi\Filelib\File\File'))
                  ->will($this->returnValue($this->plinker));

        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        $publisher->setFilelib($this->filelib);

        $publisher->unpublish($file);

        $this->assertFileNotExists($expectedPath);

    }


    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function unpublishVersionShouldUnpublishFileVersion($file, $expectedPath, $expectedVersionPath, $expectedRealPath, $expectedRelativePath)
    {
        $this->createFile($expectedRealPath, $expectedVersionPath);

        $this->assertFileExists($expectedVersionPath);

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\CopyFilesystemPublisher')
                          ->setMethods(array('getLinkerForFile'))
                          ->getMock();

        $publisher->expects($this->atLeastOnce())->method('getLinkerForFile')
                  ->with($this->isInstanceOf('Xi\Filelib\File\File'))
                  ->will($this->returnValue($this->plinker));

        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        $publisher->setFilelib($this->filelib);

        $publisher->unpublishVersion($file, $this->versionProvider->getIdentifier(), $this->versionProvider);

        $this->assertFileNotExists($expectedVersionPath);

    }






}
