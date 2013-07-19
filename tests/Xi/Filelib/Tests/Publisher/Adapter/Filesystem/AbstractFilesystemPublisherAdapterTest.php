<?php

namespace Xi\Filelib\Tests\Publisher\Adapter\Filesystem;

use Xi\Filelib\File\File;
use Xi\Filelib\Publisher\Adapter\Filesystem\AbstractFilesystemPublisherAdapter;
use Xi\Filelib\Tests\TestCase;

class AbstractFilesystemPublisherAdapterTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        chmod(ROOT_TESTS . '/data/publisher/unwritable_dir', 0444);

    }

    public function tearDown()
    {
        parent::tearDown();
        chmod(ROOT_TESTS . '/data/publisher/unwritable_dir', 0755);

    }

    /**
     * @test
     */
    public function shouldInitializeCorrectly()
    {
        $dirPerm = "777";
        $filePerm = "666";

        $publicRoot = ROOT_TESTS . '/data/publisher/public';
        $baseUrl = 'http://dr-kobros.com/files';

        $publisher = $this->getMockedAdapter($publicRoot, $filePerm, $dirPerm, $baseUrl);

        $this->assertEquals(0777, $publisher->getDirectoryPermission());
        $this->assertEquals(0666, $publisher->getFilePermission());
        $this->assertEquals($publicRoot, $publisher->getPublicRoot());
        $this->assertEquals($baseUrl, $publisher->getBaseUrl());
    }

    /**
     * @test
     */
    public function shouldInitializeCorrectlyWithDefaults()
    {
        $publicRoot = ROOT_TESTS . '/data/publisher/public';

        $publisher = $this->getMockedAdapter($publicRoot);

        $this->assertEquals(0700, $publisher->getDirectoryPermission());
        $this->assertEquals(0600, $publisher->getFilePermission());
        $this->assertEquals($publicRoot, $publisher->getPublicRoot());
        $this->assertEquals('', $publisher->getBaseUrl());
    }

    /**
     * @test
     */
    public function getUrlShouldReturnCorrectUrl()
    {

        $linker = $this->getMockedLinker();
        $linker
            ->expects($this->once())
            ->method('getLink')
            ->will($this->returnValue('tussin/lussun/tussi.jpg'));

        $file = $this->getMockedFile();
        $file->expects($this->any())->method('getId')->will($this->returnValue(1));

        $publisher = $this->getMockedAdapter(
            ROOT_TESTS . '/data/publisher/public', "777", "666", 'http://diktaattoriporssi.com'
        );

        $this->assertEquals(
            'http://diktaattoriporssi.com/tussin/lussun/tussi.jpg',
            $publisher->getUrl($file, $linker)
        );
    }

    /**
     * @test
     */
    public function getUrlVersionShouldReturnCorrectUrlVersion()
    {
        $linker = $this->getMockedLinker();
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

        $file = $this->getMockedFile();
        $file->expects($this->any())->method('getId')->will($this->returnValue(1));

        $publisher = $this->getMockedAdapter(
            ROOT_TESTS . '/data/publisher/public', "777", "666", 'http://diktaattoriporssi.com'
        );

        $versionProvider = $this->getMockedVersionProvider('xooxer');

        $this->assertEquals(
            'http://diktaattoriporssi.com/tussin/lussun/tussi-xooxer.jpg',
            $publisher->getUrlVersion($file, $versionProvider, $linker)
        );
    }

    /**
     * @param $publicRoot
     * @param int $filePermission
     * @param int $directoryPermission
     * @param string $baseUrl
     * @return AbstractFilesystemPublisherAdapter
     */
    public function getMockedAdapter($publicRoot, $filePermission = "600", $directoryPermission = "700", $baseUrl = '')
    {
        $adapter = $this
            ->getMockBuilder('Xi\Filelib\Publisher\Adapter\Filesystem\AbstractFilesystemPublisherAdapter')
            ->setMethods(
                array(
                    'publish',
                    'unpublish',
                    'publishVersion',
                    'unpublishVersion',
                    'getLinkerForFile',
                    'setDependencies'
                )
            )
            ->setConstructorArgs(array($publicRoot, $filePermission, $directoryPermission, $baseUrl))
            ->getMock();

        return $adapter;

    }

}
