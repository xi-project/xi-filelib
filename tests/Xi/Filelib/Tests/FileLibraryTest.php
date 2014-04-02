<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Authorization\AuthorizationPlugin;
use Xi\Filelib\Backend\Cache\Cache;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Profile\FileProfile;
use Xi\Filelib\Events;

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
        $commander = $this->getMockedCommander();
        $commander
            ->expects($this->once())
            ->method('setQueue')
            ->with($this->isInstanceOf('Pekkis\Queue\SymfonyBridge\EventDispatchingQueue'));

        $filelib = new FileLibrary(
            $this->getMockedStorage(),
            $this->getMockedPlatform(),
            $this->getMockedEventDispatcher(),
            $commander
        );
        $this->assertNull($filelib->getQueue());

        $adapter = $this->getMockedQueueAdapter();
        $this->assertSame($filelib, $filelib->createQueueFromAdapter($adapter));
        $this->assertInstanceOf('Pekkis\Queue\SymfonyBridge\EventDispatchingQueue', $filelib->getQueue());
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
    public function getsResourceRepository()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());
        $rere = $filelib->getResourceRepository();
        $this->assertInstanceOf('Xi\Filelib\Resource\ResourceRepository', $rere);
    }

    /**
     * @test
     */
    public function getFileRepositoryShouldWork()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());
        $fop = $filelib->getFileRepository();

        $this->assertInstanceOf('Xi\Filelib\File\FileRepository', $fop);
    }

    /**
     * @test
     */
    public function getFolderRepositoryShouldWork()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());
        $fop = $filelib->getFolderRepository();

        $this->assertInstanceOf('Xi\Filelib\Folder\FolderRepository', $fop);
    }

    /**
     * @test
     */
    public function addedProfileShouldBeReturned()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());

        $this->assertCount(1, $filelib->getProfiles());

        try {
            $profile = $filelib->getProfile('tussi');
            $this->fail('should have thrown exception');
        } catch (\InvalidArgumentException $e) {

            $p = new FileProfile('tussi', $this->getMockedLinker());

            $filelib->addProfile(
                $p
            );

            $this->assertSame($p, $filelib->getProfile('tussi'));
            $this->assertCount(2, $filelib->getProfiles());
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
                $this->equalTo(Events::PLUGIN_AFTER_ADD),
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
    public function addPluginShouldAddToAllProfilesIfNoProfilesAreProvided()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());

        $plugin = new AuthorizationPlugin($this->getMock('Xi\Filelib\Authorization\AuthorizationAdapter'));

        $filelib->addPlugin($plugin);

        $this->assertContains($plugin, $filelib->getProfile('default')->getPlugins());

        $filelib->addProfile(new FileProfile('sucklee'));

        $this->assertContains($plugin, $filelib->getProfile('sucklee')->getPlugins());

        $this->assertTrue($plugin->hasProfile('sucklee'));
        $this->assertTrue($plugin->hasProfile('suckler'));
    }

    /**
     * @test
     */
    public function addPluginShouldAddToOnlyProfilesProvided()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());

        $plugin = new AuthorizationPlugin($this->getMock('Xi\Filelib\Authorization\AuthorizationAdapter'));

        $filelib->addPlugin($plugin, array('sucklee', 'ducklee'));

        $this->assertNotContains($plugin, $filelib->getProfile('default')->getPlugins());

        $filelib->addProfile(new FileProfile('sucklee'));

        $this->assertContains($plugin, $filelib->getProfile('sucklee')->getPlugins());

        $this->assertTrue($plugin->hasProfile('sucklee'));
        $this->assertFalse($plugin->hasProfile('suckler'));
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
        $filelib = $this->getMockedFilelib(array('getFileRepository'));
        $fop = $this->getMockedFileRepository();

        $filelib->expects($this->any())->method('getFileRepository')->will($this->returnValue($fop));

        $folder = $this->getMockedFolder();

        $fop
            ->expects($this->once())
            ->method('upload')
            ->with('lussutus', $folder, 'tussi')
            ->will($this->returnValue('xooxer'));

        $ret = $filelib->upload('lussutus', $folder, 'tussi');
        $this->assertSame('xooxer', $ret);
    }

    /**
     * @test
     */
    public function cacheCanBeSet()
    {
        $filelib = new FileLibrary($this->getMockedStorage(), $this->getMockedPlatform());

        $adapter = $this->getMockedCacheAdapter();
        $this->assertNull($filelib->getCache());
        $this->assertSame($filelib, $filelib->createCacheFromAdapter($adapter));
        $this->assertInstanceOf('Xi\Filelib\Backend\Cache\Cache', $filelib->getCache());
    }
}
