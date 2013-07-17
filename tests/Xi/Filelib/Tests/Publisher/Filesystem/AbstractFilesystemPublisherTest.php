<?php

namespace Xi\Filelib\Tests\Publisher\Filesystem;

use Xi\Filelib\File\File;
use Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher;
use Xi\Filelib\Tests\TestCase;

class AbstractFilesystemPublisherTest extends TestCase
{

    public function setUp()
    {
        chmod(ROOT_TESTS . '/data/publisher/unwritable_dir', 0444);
        parent::setUp();
    }

    public function tearDown()
    {
        chmod(ROOT_TESTS . '/data/publisher/unwritable_dir', 0755);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function gettersAndSettersShouldWorkCorrectly()
    {
        $publisher = $this
            ->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
            ->setMethods(
                array(
                    'publish',
                    'unpublish',
                    'publishVersion',
                    'unpublishVersion',
                    'getUrl',
                    'getUrlVersion',
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(0700, $publisher->getDirectoryPermission());
        $this->assertEquals(0600, $publisher->getFilePermission());
        $this->assertEquals(null, $publisher->getPublicRoot());
        $this->assertEquals('', $publisher->getBaseUrl());

        $dirPerm = "777";
        $filePerm = "666";

        $publicRoot = ROOT_TESTS . '/data/publisher/public';
        $baseUrl = 'http://dr-kobros.com/files';

        $publisher->setDirectoryPermission($dirPerm);
        $publisher->setFilePermission($filePerm);
        $publisher->setPublicRoot($publicRoot);
        $publisher->setBaseUrl($baseUrl);

        $this->assertEquals(0777, $publisher->getDirectoryPermission());
        $this->assertEquals(0666, $publisher->getFilePermission());
        $this->assertEquals($publicRoot, $publisher->getPublicRoot());
        $this->assertEquals($baseUrl, $publisher->getBaseUrl());
    }

    /**
     * @test
     */
    public function getLinkerForFileShouldDelegateToOperator()
    {
        $fileop = $this->getMockedFileOperator();
        $profile = $this->getMockedFileProfile();

        $fileop
            ->expects($this->once())
            ->method('getProfile')
            ->with($this->equalTo('lusmeister'))
            ->will($this->returnValue($profile));

        $filelib = $this->getMockedFilelib(null, $fileop);

        $publisher = $this
            ->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
            ->setMethods(
                array(
                    'publish',
                    'unpublish',
                    'publishVersion',
                    'unpublishVersion',
                )
            )
            ->setConstructorArgs(array($fileop))
            ->getMock();

        $publisher->setDependencies($filelib);


        $profile->expects($this->once())->method('getLinker')->will($this->returnValue('luss'));
        $file = File::create(array('profile' => 'lusmeister'));

        $linker = $publisher->getLinkerForFile($file);
        $this->assertEquals('luss', $linker);
    }

    /**
     * @test
     */
    public function getUrlShouldReturnCorrectUrl()
    {

        $linker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();
        $linker
            ->expects($this->once())
            ->method('getLink')
            ->will(
                $this->returnCallback(
                    function ($file) {
                        return 'tussin/lussun/tussi.jpg';
                    }
                )
            );

        $file = $this->getMockBuilder('Xi\Filelib\File\File')->getMock();
        $file->expects($this->any())->method('getId')->will($this->returnValue(1));

        $publisher = $this
            ->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
            ->setMethods(
                array(
                    'publish',
                    'unpublish',
                    'publishVersion',
                    'unpublishVersion',
                    'getLinkerForFile',
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $publisher
            ->expects($this->atLeastOnce())
            ->method('getLinkerForFile')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue($linker));

        $publisher
            ->setBaseUrl('http://diktaattoriporssi.com');

        $this->assertEquals(
            'http://diktaattoriporssi.com/tussin/lussun/tussi.jpg',
            $publisher->getUrl($file)
        );
    }

    /**
     * @test
     */
    public function getUrlVersionShouldReturnCorrectUrlVersion()
    {

        $linker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();
        $linker
            ->expects($this->once())
            ->method('getLinkVersion')
            ->will(
                $this->returnCallback(
                    function ($file, $version, $extension) {
                        return 'tussin/lussun/tussi-' . $version . '.jpg';
                    }
                )
            );

        $file = $this->getMockBuilder('Xi\Filelib\File\File')->getMock();
        $file->expects($this->any())->method('getId')->will($this->returnValue(1));

        $publisher = $this
            ->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
            ->setMethods(
                array(
                    'publish',
                    'unpublish',
                    'publishVersion',
                    'unpublishVersion',
                    'getLinkerForFile'
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $publisher
            ->expects($this->atLeastOnce())
            ->method('getLinkerForFile')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue($linker));

        $versionProvider = $this
            ->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\VersionProvider')
            ->getMock();

        $versionProvider
            ->expects($this->once())->method('getIdentifier')
            ->will($this->returnValue('xooxer'));

        $publisher->setBaseUrl('http://diktaattoriporssi.com');
        $this->assertEquals(
            'http://diktaattoriporssi.com/tussin/lussun/tussi-xooxer.jpg',
            $publisher->getUrlVersion($file, $versionProvider->getIdentifier(), $versionProvider)
        );
    }

    /**
     * @test
     */
    public function setPublicRootShouldThrowExceptionWhenDirectoryDoesNotExist()
    {
        $publisher = $this
           ->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
            ->setMethods(
                array(
                    'publish',
                    'unpublish',
                    'publishVersion',
                    'unpublishVersion',
                    'getFilelib',
                    'setFilelib'
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $unexistingDir = ROOT_TESTS . '/data/publisher/unexisting_dir';

        $this->setExpectedException('LogicException');
        $publisher->setPublicRoot($unexistingDir);
    }

    /**
     * @test
     */
    public function setPublicRootShouldThrowExceptionWhenDirectoryIsNotReadable()
    {
        $publisher = $this
            ->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
            ->setMethods(
                array(
                    'publish',
                    'unpublish',
                    'publishVersion',
                    'unpublishVersion',
                    'getFilelib',
                    'setFilelib'
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $unwritableDir = ROOT_TESTS . '/data/publisher/unwritable_dir';

        $this->setExpectedException('LogicException');
        $publisher->setPublicRoot($unwritableDir);
    }
}
