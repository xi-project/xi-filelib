<?php

namespace Xi\Tests\Filelib\Publisher\Filesystem;


use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher;
use Xi\Tests\Filelib\TestCase;

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
        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
        ->setMethods(array(
            'publish',
            'unpublish',
            'publishVersion',
            'unpublishVersion',
            'getUrl',
            'getUrlVersion',
            'getFilelib',
            'setFilelib'
        ))
        ->getMock();

        $this->assertEquals(0700, $publisher->getDirectoryPermission());
        $this->assertEquals(0600, $publisher->getFilePermission());
        $this->assertEquals(null, $publisher->getPublicRoot());
        $this->assertEquals('', $publisher->getBaseUrl());

        // 777 permissions always help!1!
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
    public function getLinkerForFileShouldDelegateToFilelib()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $fileop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();
        $profile = $this->getMock('Xi\Filelib\File\FileProfile');

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
        ->setMethods(array(
            'publish',
            'unpublish',
            'publishVersion',
            'unpublishVersion',
            'getFilelib',
            'setFilelib'
        ))
        ->getMock();

        $publisher->expects($this->once())->method('getFilelib')->will($this->returnValue($filelib));
        $filelib->expects($this->once())->method('getFileOperator')->will($this->returnValue($fileop));
        $fileop->expects($this->once())->method('getProfile')->with($this->equalTo('lusmeister'))->will($this->returnValue($profile));
        $profile->expects($this->once())->method('getLinker');

        $file = File::create(array('profile' => 'lusmeister'));

        $linker = $publisher->getLinkerForFile($file);

    }


    /**
     * @test
     */
    public function getUrlShouldReturnCorrectUrl()
    {

        $linker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();
        $linker->expects($this->once())->method('getLink')
                ->will($this->returnCallback(function($file) { return 'tussin/lussun/tussi.jpg'; }));

        $file = $this->getMockBuilder('Xi\Filelib\File\File')->getMock();
        $file->expects($this->any())->method('getId')->will($this->returnValue(1));

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
        ->setMethods(array(
            'publish',
            'unpublish',
            'publishVersion',
            'unpublishVersion',
            'getFilelib',
            'setFilelib',
            'getLinkerForFile',
        ))
        ->getMock();

        $publisher->expects($this->atLeastOnce())
                  ->method('getLinkerForFile')
                  ->with($this->isInstanceOf('Xi\Filelib\File\File'))
                  ->will($this->returnValue($linker));

        $publisher->setBaseUrl('http://diktaattoriporssi.com');
        $this->assertEquals('http://diktaattoriporssi.com/tussin/lussun/tussi.jpg', $publisher->getUrl($file));


    }

    /**
     * @test
     */
    public function getUrlVersionShouldReturnCorrectUrlVersion()
    {

        $linker = $this->getMockBuilder('Xi\Filelib\Linker\Linker')->getMock();
        $linker->expects($this->once())->method('getLinkVersion')
                ->will($this->returnCallback(function($file, $version, $extension) { return 'tussin/lussun/tussi-' . $version . '.jpg'; }));


        $file = $this->getMockBuilder('Xi\Filelib\File\File')->getMock();
        $file->expects($this->any())->method('getId')->will($this->returnValue(1));

        $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
        ->setMethods(array(
            'publish',
            'unpublish',
            'publishVersion',
            'unpublishVersion',
            'getFilelib',
            'setFilelib',
            'getLinkerForFile'
        ))
        ->getMock();

        $publisher->expects($this->atLeastOnce())
                  ->method('getLinkerForFile')
                  ->with($this->isInstanceOf('Xi\Filelib\File\File'))
                  ->will($this->returnValue($linker));

        $versionProvider = $this->getMockBuilder('Xi\Filelib\Plugin\VersionProvider\VersionProvider')->getMock();
        $versionProvider->expects($this->once())->method('getIdentifier')
        ->will($this->returnCallback(function() { return 'xooxer'; }));

        $publisher->setBaseUrl('http://diktaattoriporssi.com');

        $this->assertEquals('http://diktaattoriporssi.com/tussin/lussun/tussi-xooxer.jpg', $publisher->getUrlVersion($file, $versionProvider->getIdentifier(), $versionProvider));


    }



    /**
     * @test
     */
    public function setPublicRootShouldThrowExceptionWhenDirectoryDoesNotExist()
    {
       $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
        ->setMethods(array(
            'publish',
            'unpublish',
            'publishVersion',
            'unpublishVersion',
            'getFilelib',
            'setFilelib'
        ))
        ->getMock();

        $unexistingDir = ROOT_TESTS . '/data/publisher/unexisting_dir';

        try {
            $publisher->setPublicRoot($unexistingDir);

            $this->fail("Expected \LogicException!");

        } catch (\LogicException $e) {

            $this->assertRegExp("/does not exist/", $e->getMessage());

        }


    }


    /**
     * @test
     */
    public function setPublicRootShouldThrowExceptionWhenDirectoryIsNotReadable()
    {
       $publisher = $this->getMockBuilder('Xi\Filelib\Publisher\Filesystem\AbstractFilesystemPublisher')
        ->setMethods(array(
            'publish',
            'unpublish',
            'publishVersion',
            'unpublishVersion',
            'getFilelib',
            'setFilelib'
        ))
        ->getMock();

        $unwritableDir = ROOT_TESTS . '/data/publisher/unwritable_dir';

        try {
            $publisher->setPublicRoot($unwritableDir);

            $this->fail("Expected \LogicException!");

        } catch (\LogicException $e) {

            $this->assertRegExp("/not writeable/", $e->getMessage());

        }

    }

}
