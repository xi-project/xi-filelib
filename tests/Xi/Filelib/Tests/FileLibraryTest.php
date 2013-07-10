<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileProfile;

class FileLibraryTest extends TestCase
{
    private $dirname;

    /**
     * @var FileLibrary
     */
    private $filelib;

    public function setUp()
    {
        parent::setUp();
        $this->dirname = ROOT_TESTS . '/data/publisher/unwritable_dir';

        chmod($this->dirname, 0444);

    }

    public function tearDown()
    {
        parent::tearDown();
        chmod($this->dirname, 0755);

    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\FileLibrary'));
    }

    /**
     * @test
     */
    public function storageGetterShouldWork()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());
        $this->assertInstanceOf('Xi\Filelib\Storage\Storage', $filelib->getStorage());
    }

    /**
     * @test
     */
    public function queueSetterAndGetterShouldWork()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());
        $obj = $this->getMockedQueue();
        $this->assertNull($filelib->getQueue());
        $this->assertSame($filelib, $filelib->setQueue($obj));
        $this->assertSame($obj, $filelib->getQueue());
    }

    /**
     * @test
     */
    public function platformGetterShouldWork()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());
        $this->assertInstanceOf('Xi\Filelib\Backend\Platform\Platform', $filelib->getPlatform());
    }

    /**
     * @test
     */
    public function backendGetterShouldWork()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());
        $this->assertInstanceOf('Xi\Filelib\Backend\Backend', $filelib->getBackend());
    }

    /**
     * @test
     */
    public function tempDirShouldDefaultToSystemTempDir()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());
        $this->assertEquals(sys_get_temp_dir(), $filelib->getTempDir());
    }

    /**
     * @test
     */
    public function setTempDirShouldFailWhenDirectoryDoesNotExists()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());

        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf(
                'Temp dir "%s" is not writable or does not exist',
                ROOT_TESTS . '/nonexisting_directory'
            )
        );

        $filelib->setTempDir(ROOT_TESTS . '/nonexisting_directory');
    }

    /**
     * @test
     */
    public function setTempDirShouldFailWhenDirectoryIsNotWritable()
    {
        $dirname = ROOT_TESTS . '/data/publisher/unwritable_dir';
        $this->assertTrue(is_dir($this->dirname));
        $this->assertFalse(is_writable($this->dirname));

        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());

        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf(
                'Temp dir "%s" is not writable or does not exist',
                $dirname
            )
        );

        $filelib->setTempDir($dirname);
    }

    /**
     * @test
     */
    public function getFileOperatorShouldWork()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());
        $fop = $filelib->getFileOperator();

        $this->assertInstanceOf('Xi\Filelib\File\FileOperator', $fop);
    }

    /**
     * @test
     */
    public function getFolderOperatorShouldWork()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());
        $fop = $filelib->getFolderOperator();

        $this->assertInstanceOf('Xi\Filelib\Folder\FolderOperator', $fop);
    }

    /**
     * @test
     */
    public function addedProfileShouldBeReturned()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());

        $this->assertCount(0, $filelib->getProfiles());

        try {
            $profile = $filelib->getProfile('tussi');
            $this->fail('should have thrown exception');
        } catch (\InvalidArgumentException $e) {

            $p = new FileProfile('tussi', $this->getMockedLinker());

            $filelib->addProfile(
                $p
            );

            $this->assertSame($p, $filelib->getProfile('tussi'));
            $this->assertCount(1, $filelib->getProfiles());
        }
    }

    /**
     * @test
     */
    public function addPluginShouldFirePluginAddEventAndAddPluginAsSubscriber()
    {
        $ed = $this->getMockedEventDispatcher();
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform(), $ed);

        $plugin = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');

        $ed
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo('xi_filelib.plugin.add'),
                $this->isInstanceOf('Xi\Filelib\Event\PluginEvent')
            );

        $ed
            ->expects($this->once())
            ->method('addSubscriber')
            ->with($this->equalTo($plugin));

        $filelib->addPlugin($plugin);
    }

    /**
     * @test
     */
    public function getEventDispatcherShouldWork()
    {
        $ed = $this->getMockedEventDispatcher();
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform(), $ed);
        $this->assertSame($ed, $filelib->getEventDispatcher());
    }

    /**
     * @test
     */
    public function uploadShortcutShouldDelegate()
    {
        $filelib = $this->getMockedFilelib(array('getFileOperator'));
        $fop = $this->getMockedFileOperator();

        $filelib->expects($this->any())->method('getFileOperator')->will($this->returnValue($fop));

        $folder = $this->getMockedFolder();

        $fop
            ->expects($this->once())
            ->method('upload')
            ->with('lussutus', $folder, 'tussi')
            ->will($this->returnValue('xooxer'));

        $ret = $filelib->upload('lussutus', $folder, 'tussi');
        $this->assertSame('xooxer', $ret);
    }




}
