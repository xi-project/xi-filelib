<?php

namespace Xi\Tests\Filelib\Publisher\Filesystem;

use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher;

class SymlinkFilesystemPublisherTest extends TestCase
{

    protected $plinker;

    public function setUp()
    {
        parent::setUp();

        $linker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();
        $linker->expects($this->any())->method('getLinkVersion')
                ->will($this->returnCallback(function($file, $version) {

                    switch ($file->getId()) {

                        case 1:
                            $prefix = 'lussin/tussin';
                            break;
                        case 2:
                            $prefix = 'lussin/tussin/jussin/pussin';
                            break;
                        case 3:
                            $prefix = 'tohtori/vesalan/suuri/otsa';
                            break;
                        case 4:
                            $prefix = 'lussen/hof';
                            break;
                        case 5:
                            $prefix = '';
                            break;

                    }


                    return $prefix . '/' . $file->getId() . '-' . $version . '.lus';

                }));
        $linker->expects($this->any())->method('getLink')
                ->will($this->returnCallback(function($file) {

                    switch ($file->getId()) {

                        case 1:
                            $prefix = 'lussin/tussin';
                            break;
                        case 2:
                            $prefix = 'lussin/tussin/jussin/pussin';
                            break;
                        case 3:
                            $prefix = 'tohtori/vesalan/suuri/otsa';
                            break;
                        case 4:
                            $prefix = 'lussen/hof';
                            break;
                        case 5:
                            $prefix = '';
                            break;

                    }

                    return $prefix . '/' . $file->getId() . '.lus';
                 }));

        $this->plinker = $linker;

    }



    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $publisher = new SymlinkFilesystemPublisher();

        $this->assertNull($publisher->getRelativePathToRoot());
        $relativePath = '../private';
        $publisher->setRelativePathToRoot($relativePath);
        $this->assertEquals($relativePath, $publisher->getRelativePathToRoot());
    }



    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function getRelativePathToShouldFailWhenRelativePathToRootIsMissing()
    {
        $publisher = new SymlinkFilesystemPublisher();
        $file = File::create(array('id' => 1));
        $relativePath = $publisher->getRelativePathTo($file);
    }


    /**
     * @test
     * @expectedException \Xi\Filelib\FilelibException
     */
    public function getRelativePathToVersionShouldFailWhenRelativePathToRootIsMissing()
    {
        $publisher = new SymlinkFilesystemPublisher();
        $file = File::create(array('id' => 1));
        $relativePath = $publisher->getRelativePathToVersion($file, $this->versionProvider);
    }



    public function provideDataForRelativePathTest()
    {
        return array(

            array(
                File::create(array('id' => 1)),
                0,
                '../private/1/1',
            ),
            array(
                File::create(array('id' => 2)),
                3,
                '../../../../private/2/2/2',
            ),
            array(
                File::create(array('id' => 3)),
                2,
                '../../../private/3/3/3/3',
            ),
            array(
                File::create(array('id' => 4)),
                1,
                '../../private/666/4',
            ),
        );
    }


    /**
     * @test
     * @dataProvider provideDataForRelativePathTest
     */
    public function getRelativePathToShouldReturnRelativePathToFile($file, $levelsDown, $expectedRelativePath)
    {
        $this->filelib->setStorage($this->storage);

        $publisher = new SymlinkFilesystemPublisher();
        $publisher->setFilelib($this->filelib);
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        $publisher->setRelativePathToRoot('../private');

        $this->assertEquals($expectedRelativePath, $publisher->getRelativePathTo($file, $levelsDown));

    }


    /**
     * @test
     * @dataProvider provideDataForRelativePathTest
     */
    public function getRelativePathToVersionShouldReturnRelativePathToFile($file, $levelsDown, $expectedRelativePath)
    {
        $this->filelib->setStorage($this->storage);

        $publisher = new SymlinkFilesystemPublisher();
        $publisher->setFilelib($this->filelib);
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        $publisher->setRelativePathToRoot('../private');

        $this->assertEquals($expectedRelativePath, $publisher->getRelativePathToVersion($file, $this->versionProvider, $levelsDown));

    }



    public function provideDataForPublishingTests()
    {
        $files = array();

        for ($x = 1; $x <= 5; $x++) {
            $file = $this->getMockBuilder('Xi\Filelib\File\File')->getMock();

            $file->expects($this->any())->method('getProfile')
                    ->will($this->returnValue('profile'));

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
            ),
            array(
                $files[1],
                ROOT_TESTS . '/data/publisher/public/lussin/tussin/jussin/pussin/2.lus',
                ROOT_TESTS . '/data/publisher/public/lussin/tussin/jussin/pussin/2-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/2/2/2',
                '../../../../../private/2/2/2',
            ),
            array(
                $files[2],
                ROOT_TESTS . '/data/publisher/public/tohtori/vesalan/suuri/otsa/3.lus',
                ROOT_TESTS . '/data/publisher/public/tohtori/vesalan/suuri/otsa/3-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/3/3/3/3',
                '../../../../../private/3/3/3/3',
            ),
            array(
                $files[3],
                ROOT_TESTS . '/data/publisher/public/lussen/hof/4.lus',
                ROOT_TESTS . '/data/publisher/public/lussen/hof/4-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/666/4',
                '../../../private/666/4',
            ),
            array(
                $files[4],
                ROOT_TESTS . '/data/publisher/public/5.lus',
                ROOT_TESTS . '/data/publisher/public/5-xooxer.lus',
                ROOT_TESTS . '/data/publisher/private/1/5',
                '../private/1/5',
            ),

        );

        return $ret;

    }

    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function publishShouldPublishFileWithoutRelativePaths($file, $expectedPath, $expectedVersionPath, $expectedRealPath)
    {
        $this->filelib->setStorage($this->storage);

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher')
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

        $this->assertTrue($sfi->isLink(), "File '{$expectedPath}' is not a symbolic link");

        $this->assertTrue($sfi->isReadable(), "File '{$expectedPath}' is not a readable symbolic link");

        $this->assertEquals($expectedRealPath, $sfi->getRealPath(), "File '{$expectedPath}' points to wrong file");

    }


    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function publishShouldPublishFileVersionWithoutRelativePaths($file, $expectedPath, $expectedVersionPath, $expectedRealPath)
    {
        $this->filelib->setStorage($this->storage);

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher')
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

        $this->assertTrue($sfi->isLink(), "File '{$expectedVersionPath}' is not a symbolic link");

        $this->assertTrue($sfi->isReadable(), "File '{$expectedVersionPath}' is not a readable symbolic link");

        $this->assertEquals($expectedRealPath, $sfi->getRealPath(), "File '{$expectedPath}' points to wrong file");

    }


    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function publishShouldPublishFileWithRelativePaths($file, $expectedPath, $expectedVersionPath, $expectedRealPath, $expectedRelativePath)
    {
        $this->filelib->setStorage($this->storage);

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher')
                          ->setMethods(array('getLinkerForFile'))
                          ->getMock();

        $publisher->expects($this->atLeastOnce())->method('getLinkerForFile')
                  ->with($this->isInstanceOf('Xi\Filelib\File\File'))
                  ->will($this->returnValue($this->plinker));

        $publisher->setRelativePathToRoot('../private');
        $publisher->setFilelib($this->filelib);
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        // $publisher->setRelativePathToRoot('../private');

        $publisher->publish($file);

        $sfi = new \SplFileInfo($expectedPath);

        $this->assertTrue($sfi->isLink(), "File '{$expectedPath}' is not a symbolic link");

        $this->assertTrue($sfi->isReadable(), "File '{$expectedPath}' is not a readable symbolic link");

        $this->assertEquals($expectedRealPath, $sfi->getRealPath(), "File '{$expectedPath}' points to wrong file");

        $this->assertEquals($expectedRelativePath, $sfi->getLinkTarget(), "Relative path '{$expectedRelativePath}' points to wrong place");

    }



    /**
     * @test
     * @dataProvider provideDataForPublishingTests
     */
    public function publishShouldPublishFileVersionWithRelativePaths($file, $expectedPath, $expectedVersionPath, $expectedRealPath, $expectedRelativePath)
    {
        $this->filelib->setStorage($this->storage);

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher')
                          ->setMethods(array('getLinkerForFile'))
                          ->getMock();

        $publisher->expects($this->atLeastOnce())->method('getLinkerForFile')
                  ->with($this->isInstanceOf('Xi\Filelib\File\File'))
                  ->will($this->returnValue($this->plinker));



        $publisher->setFilelib($this->filelib);
        $publisher->setPublicRoot(ROOT_TESTS . '/data/publisher/public');
        $publisher->setRelativePathToRoot('../private');

        $publisher->publishVersion($file, $this->versionProvider->getIdentifier(), $this->versionProvider);

        $sfi = new \SplFileInfo($expectedVersionPath);

        $this->assertTrue($sfi->isLink(), "File '{$expectedVersionPath}' is not a symbolic link");

        $this->assertTrue($sfi->isReadable(), "File '{$expectedVersionPath}' is not a readable symbolic link");

        $this->assertEquals($expectedRealPath, $sfi->getRealPath(), "File '{$expectedPath}' points to wrong file");

        $this->assertEquals($expectedRelativePath, $sfi->getLinkTarget(), "Relative path '{$expectedRelativePath}' points to wrong place");

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
    public function unpublishShouldUnpublishFile($file, $expectedPath, $expectedVersionPath, $expectedRealPath, $expectedRelativePath)
    {
        $this->createLink($expectedRealPath, $expectedPath);
        $this->assertFileExists($expectedPath);

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher')
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
        $this->createLink($expectedRealPath, $expectedVersionPath);
        $this->assertFileExists($expectedVersionPath);

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\SymlinkFilesystemPublisher')
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
