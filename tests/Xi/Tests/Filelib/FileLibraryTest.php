<?php

namespace Xi\Tests\Filelib;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileProfile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class FileLibraryTest extends TestCase
{
    private $dirname;

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
    public function storageSetterAndGetterShouldWorkAsExpected()
    {
        $filelib = new FileLibrary();
        $storage = $this->getMock('Xi\Filelib\Storage\Storage');

        $this->assertNull($filelib->getStorage());
        $this->assertSame($filelib, $filelib->setStorage($storage));
        $this->assertSame($storage, $filelib->getStorage());
    }


    /**
     * @test
     */
    public function publisherSetterAndGetterShouldWorkAsExpected()
    {
        $filelib = new FileLibrary();
        $obj = $this->getMockForAbstractClass('Xi\Filelib\Publisher\Publisher');
        $obj->expects($this->once())->method('setFilelib')->with($this->isInstanceOf('Xi\Filelib\FileLibrary'));
        $this->assertEquals(null, $filelib->getPublisher());
        $this->assertSame($filelib, $filelib->setPublisher($obj));
        $this->assertSame($obj, $filelib->getPublisher());
    }


    /**
     * @test
     */
    public function queueSetterAndGetterShouldWorkAsExpected()
    {
        $filelib = new FileLibrary();
        $obj = $this->getMockForAbstractClass('Xi\Filelib\Queue\Queue');
        $this->assertEquals(null, $filelib->getQueue());
        $this->assertSame($filelib, $filelib->setQueue($obj));
        $this->assertSame($obj, $filelib->getQueue());
    }



    /**
     * @test
     */
    public function aclSetterAndGetterShouldWorkAsExpected()
    {
        $filelib = new FileLibrary();
        $obj = $this->getMockForAbstractClass('Xi\Filelib\Acl\Acl');
        // @todo: maybe $obj->expects($this->once())->method('setFilelib')->with($this->isInstanceOf('Xi\Filelib\FileLibrary'));
        $this->assertEquals(null, $filelib->getAcl());
        $this->assertSame($filelib, $filelib->setAcl($obj));
        $this->assertSame($obj, $filelib->getAcl());
    }

    /**
     * @test
     */
    public function tempDirShouldDefaultToSystemTempDir()
    {
        $filelib = new FileLibrary();
        $this->assertEquals(sys_get_temp_dir(), $filelib->getTempDir());
    }

    /**
     * @test
     */
    public function setTempDirShouldFailWhenDirectoryDoesNotExists()
    {
        $filelib = new FileLibrary();

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

        $filelib = new FileLibrary();

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
     *
     */
    public function fileShouldDelegateToGetFileOperator()
    {
        $filelib = $this->getMockBuilder('Xi\Filelib\FileLibrary')->setMethods(array('getFileOperator'))->getMock();
        $filelib->expects($this->once())->method('getFileOperator');
        @$filelib->file();
    }

    /**
     * @test
     *
     */
    public function folderShouldDelegateToGetFolderOperator()
    {
        $filelib = $this->getMockBuilder('Xi\Filelib\FileLibrary')->setMethods(array('getFolderOperator'))->getMock();
        $filelib->expects($this->once())->method('getFolderOperator');
        @$filelib->folder();
    }

    /**
     * @test
     */
    public function getFileOperatorShouldDefaultToFileOperator()
    {
        $filelib = new FileLibrary();
        $fop = $filelib->getFileOperator();

        $this->assertEquals('Xi\Filelib\File\FileOperator', get_class($fop));
    }

    /**
     * @test
     */
    public function getFolderOperatorShouldDefaultToFolderOperator()
    {
        $filelib = new FileLibrary();
        $fop = $filelib->getFolderOperator();
        $this->assertEquals('Xi\Filelib\Folder\FolderOperator', get_class($fop));
    }

    /**
     * @test
     */
    public function setFileOperatorShouldOverrideFileOperator()
    {
        $mock = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();
        $filelib = new FileLibrary();
        $this->assertSame($filelib, $filelib->setFileOperator($mock));
        $this->assertSame($mock, $filelib->getFileOperator());
    }

    /**
     * @test
     */
    public function setFolderOperatorShouldOverrideFolderOperator()
    {
        $mock = $this->getMockBuilder('Xi\Filelib\Folder\FolderOperator')->disableOriginalConstructor()->getMock();
        $filelib = new FileLibrary();
        $this->assertSame($filelib, $filelib->setFolderOperator($mock));
        $this->assertSame($mock, $filelib->getFolderOperator());
    }

    /**
     * @test
     */
    public function getProfilesShouldDelegateToFileOperator()
    {
        $fop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();
        $fop->expects($this->once())->method('getProfiles');

        $filelib = new FileLibrary();
        $filelib->setFileOperator($fop);
        $filelib->getProfiles();



    }

    /**
     * @test
     */
    public function addProfileShouldDelegateToFileOperator()
    {

        $profile = $this->getMock('Xi\Filelib\File\FileProfile');

        $fop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();
        $fop->expects($this->once())->method('addProfile')->with($this->equalTo($profile));

        $filelib = new FileLibrary();
        $filelib->setFileOperator($fop);

        $filelib->addProfile($profile);

    }



    /**
     * @test
     */
    public function addPluginShouldFirePluginAddEvent()
    {
        $fop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();

        $plugin = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $plugin->expects($this->once())->method('init');

        $filelib = new FileLibrary();
        $filelib->setFileOperator($fop);

        $eventDispatcher = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $eventDispatcher->expects($this->once())->method('dispatch')
                        ->with($this->equalTo('plugin.add'), $this->isInstanceOf('Xi\Filelib\Event\PluginEvent'));

        $filelib->setEventDispatcher($eventDispatcher);
        $filelib->addPlugin($plugin);
    }


    /**
     * @test
     */
    public function addPluginShouldAddPluginAsSubscriber()
    {
        $fop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();
        $plugin = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');

        $filelib = new FileLibrary();
        $filelib->setFileOperator($fop);

        $eventDispatcher = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $eventDispatcher->expects($this->once())->method('addSubscriber')
                        ->with($this->equalTo($plugin));

        $filelib->setEventDispatcher($eventDispatcher);

        $filelib->addPlugin($plugin);
    }



    /**
     * @test
     */
    public function getEventDispatcherShouldDefaultToSymfonyDefaultImplementation()
    {
        $filelib = new FileLibrary();
        $dispatcher = $filelib->getEventDispatcher();

        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventDispatcher', $dispatcher);

    }


    /**
     * @test
     */
    public function getEventDispatcherShouldObeySetter()
    {
        $filelib = new FileLibrary();

        $mock = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->assertSame($filelib, $filelib->setEventDispatcher($mock));

        $dispatcher = $filelib->getEventDispatcher();

        $this->assertSame($mock, $dispatcher);

    }



    /**
     * @test
     */
    public function dispatchInitEventShouldDelegateToEventDispatcherAndDispatchEvent()
    {
        $filelib = $this->getMockBuilder('Xi\Filelib\FileLibrary')
                        ->setMethods(array('getEventDispatcher'))
                        ->getMock();

        $dispatcher = $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $filelib->expects($this->once())->method('getEventDispatcher')->will($this->returnValue($dispatcher));

        $dispatcher->expects($this->once())->method('dispatch')->with($this->equalTo('filelib.init'), $this->isInstanceOf('Xi\Filelib\Event\FilelibEvent'));

        $filelib->dispatchInitEvent();

    }



}
