<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\Authorization\AuthorizationPlugin;
use Xi\Filelib\Backend\Cache\Cache;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\RandomizeNamePlugin;
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
    public function correctVersion()
    {
        $this->assertEquals('0.12.0-dev', FileLibrary::VERSION);
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
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter());
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
            $this->getMockedStorageAdapter(),
            $this->getMockedBackendAdapter(),
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
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter());
        $this->assertInstanceOf('Xi\Filelib\Backend\Adapter\BackendAdapter', $filelib->getBackendAdapter());
    }

    /**
     * @test
     */
    public function backendGetterShouldWork()
    {
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter());
        $this->assertInstanceOf('Xi\Filelib\Backend\Backend', $filelib->getBackend());
    }

    /**
     * @test
     */
    public function tempDirShouldDefaultToSystemTempDir()
    {
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter());
        $this->assertEquals(sys_get_temp_dir(), $filelib->getTempDir());
    }

    /**
     * @test
     */
    public function setTempDirShouldFailWhenDirectoryDoesNotExists()
    {
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter());

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

        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter());

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
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter());
        $rere = $filelib->getResourceRepository();
        $this->assertInstanceOf('Xi\Filelib\Resource\ResourceRepository', $rere);
    }

    /**
     * @test
     */
    public function getFileRepositoryShouldWork()
    {
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter());
        $fop = $filelib->getFileRepository();

        $this->assertInstanceOf('Xi\Filelib\File\FileRepository', $fop);
    }

    /**
     * @test
     */
    public function getFolderRepositoryShouldWork()
    {
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter());
        $fop = $filelib->getFolderRepository();

        $this->assertInstanceOf('Xi\Filelib\Folder\FolderRepository', $fop);
    }

    /**
     * @test
     */
    public function addedProfileShouldBeReturned()
    {
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter());

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
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter(), $ed);

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
    public function addPluginDelegates()
    {
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter());
        $plugin = new RandomizeNamePlugin();

        $this->assertSame($filelib, $filelib->addPlugin($plugin, array(), 'lusso'));

        $this->assertCount(1, $filelib->getPluginManager()->getPlugins());
    }


    /**
     * @test
     */
    public function getEventDispatcherShouldWork()
    {
        $ed = $this->getMockedEventDispatcher();
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter(), $ed);
        $this->assertSame($ed, $filelib->getEventDispatcher());
    }

    /**
     * @test
     */
    public function uploadFileDelegates()
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

        $ret = $filelib->uploadFile('lussutus', $folder, 'tussi');
        $this->assertSame('xooxer', $ret);
    }

    /**
     * @test
     */
    public function findFileDelegates()
    {
        $filelib = $this->getMockedFilelib(array('getFileRepository'));
        $fop = $this->getMockedFileRepository();
        $filelib->expects($this->any())->method('getFileRepository')->will($this->returnValue($fop));

        $id = 'lussendorf';

        $fop
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->will($this->returnValue('xooxer'));

        $ret = $filelib->findFile($id);
        $this->assertSame('xooxer', $ret);
    }

    /**
     * @test
     */
    public function findFilesDelegates()
    {
        $filelib = $this->getMockedFilelib(array('getFileRepository'));
        $fop = $this->getMockedFileRepository();
        $filelib->expects($this->any())->method('getFileRepository')->will($this->returnValue($fop));

        $ids = array('lussendorf', 'lussenford');

        $fop
            ->expects($this->once())
            ->method('findMany')
            ->with($ids)
            ->will($this->returnValue('xooxer'));

        $ret = $filelib->findFiles($ids);
        $this->assertSame('xooxer', $ret);
    }

    /**
     * @test
     */
    public function findFilesByDelegates()
    {
        $filelib = $this->getMockedFilelib(array('getFileRepository'));
        $fop = $this->getMockedFileRepository();
        $filelib->expects($this->any())->method('getFileRepository')->will($this->returnValue($fop));

        $finder = new FileFinder();

        $fop
            ->expects($this->once())
            ->method('findBy')
            ->with($finder)
            ->will($this->returnValue('xooxer'));

        $ret = $filelib->findFilesBy($finder);
        $this->assertSame('xooxer', $ret);
    }

    /**
     * @test
     */
    public function createFolderByUrlDelegates()
    {
        $filelib = $this->getMockedFilelib(array('getFolderRepository', 'getFileRepository'));
        $fop = $this->getMockedFolderRepository();
        $filelib->expects($this->any())->method('getFolderRepository')->will($this->returnValue($fop));

        $url = '/tenhunen/imaiseppa/tappion/karvas/kalkki';

        $fop
            ->expects($this->once())
            ->method('createByUrl')
            ->with($url)
            ->will($this->returnValue('xooxer'));

        $ret = $filelib->createFolderByUrl($url);
        $this->assertEquals('xooxer', $ret);
    }


    /**
     * @test
     */
    public function cacheCanBeSet()
    {
        $filelib = new FileLibrary($this->getMockedStorageAdapter(), $this->getMockedBackendAdapter());
        $adapter = $this->getMockedCacheAdapter();
        $this->assertSame($filelib, $filelib->createCacheFromAdapter($adapter));
        $this->assertInstanceOf('Xi\Filelib\Backend\Cache\Cache', $filelib->getCache());
    }
}
