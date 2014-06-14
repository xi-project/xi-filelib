<?php

namespace Xi\Filelib\Tests\Publisher\Adapter\Filesystem;

use Xi\Filelib\File\File;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Publisher\Adapter\Filesystem\CopyFilesystemPublisherAdapter;

class CopyFilesystemPublisherAdapterTest extends TestCase
{

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
                ->will(
                    $this->returnValue(
                        Resource::create(array('id' => $x))
                    )
                );

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
    public function publishShouldPublishFileVersion(
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
                ->expects($this->once())
                ->method('retrieveVersion')
                ->with($file->getResource(), 'xooxer')
                ->will($this->returnValue($this->resourcePaths[$file->getResource()->getId()]));
        } else {
            $this->storage
                ->expects($this->once())
                ->method('retrieveVersion')
                ->with($file, 'xooxer')
                ->will($this->returnValue($this->resourcePaths[$file->getResource()->getId()]));
        }

        $publisher = new CopyFilesystemPublisherAdapter(ROOT_TESTS . '/data/publisher/public');
        $publisher->attachTo($this->filelib);
        $publisher->publish($file, $this->version, $this->versionProvider, $this->plinker);

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
    public function unpublishShouldUnpublishFileVersion(
        $file,
        $expectedPath,
        $expectedVersionPath,
        $expectedRealPath,
        $expectedRelativePath
    ) {
        $this->createFile($expectedRealPath, $expectedVersionPath);
        $this->assertFileExists($expectedVersionPath);

        $publisher = new CopyFilesystemPublisherAdapter(ROOT_TESTS . '/data/publisher/public');
        $publisher->attachTo($this->filelib);

        $publisher->unpublish($file, $this->version, $this->versionProvider, $this->plinker);
        $this->assertFileNotExists($expectedVersionPath);
    }

}
