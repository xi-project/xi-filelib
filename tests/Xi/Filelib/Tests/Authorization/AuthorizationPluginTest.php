<?php

namespace Xi\Filelib\Tests\Authorization;

use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\FolderEvent;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Identifiable;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Authorization\AuthorizationPlugin;
use Xi\Filelib\Events as CoreEvents;
use Xi\Filelib\Publisher\Events as PublisherEvents;
use Xi\Filelib\Renderer\Events as RendererEvents;
use Xi\Filelib\Authorization\Events;

class AuthorizationPluginTest extends TestCase
{
    private $adapter;

    private $filelib;

    private $ed;

    public function setUp()
    {
        $this->adapter = $this->getMock('Xi\Filelib\Authorization\AuthorizationAdapter');
        $this->ed = $this->getMockedEventDispatcher();
        $this->filelib = $this->getMockedFilelib(null, null, null, null, $this->ed);
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Authorization\AuthorizationPlugin');
    }

    /**
     * @test
     */
    public function attachShouldDelegateToAdapter()
    {
        $plugin = new AuthorizationPlugin($this->adapter);
        $this->adapter->expects($this->once())->method('attachTo')->with($this->filelib);
        $plugin->attachTo($this->filelib);
    }


    /**
     * @test
     */
    public function pluginShouldSubscribeToCorrectEvents()
    {
        $expected = array(
            CoreEvents::PROFILE_AFTER_ADD => 'onFileProfileAdd',
            CoreEvents::FOLDER_BEFORE_WRITE_TO => 'checkFolderWrite',
            CoreEvents::FOLDER_BEFORE_DELETE => 'checkFolderWrite',
            CoreEvents::FOLDER_BEFORE_UPDATE => 'checkFolderWrite',
            CoreEvents::FILE_BEFORE_DELETE => 'checkFileWrite',
            CoreEvents::FILE_BEFORE_UPDATE => 'checkFileWrite',
            PublisherEvents::FILE_BEFORE_PUBLISH => 'checkFileAnonymousRead',
            RendererEvents::RENDERER_BEFORE_RENDER => 'checkFileRead',
        );
        $this->assertEquals($expected, AuthorizationPlugin::getSubscribedEvents());
    }

    /**
     * @return array
     */
    public function provideData()
    {
        return array(
            array('checkFileAnonymousRead', 'isFileReadableByAnonymous', 'Anonymous read', File::create(array('id' => 'gran-lusso'))),
            array('checkFileRead', 'isFileReadable', 'Read', File::create(array('id' => 'gran-lusso'))),
            array('checkFolderWrite', 'isFolderWritable', 'Write', Folder::create(array('id' => 'gran-lusso'))),
            array('checkFileWrite', 'isFileWritable', 'Write', File::create(array('id' => 'gran-lusso'))),
            array('checkFolderRead', 'isFolderReadable', 'Read', Folder::create(array('id' => 'gran-lusso'))),
        );
    }

    /**
     * @test
     * @dataProvider provideData
     */
    public function deniedPermissionShouldDispatchEventAndThrowException(
        $method,
        $expectedMethod,
        $expectedException,
        Identifiable $identifiable
    ) {
        $this->adapter->expects($this->once())->method($expectedMethod)->will($this->returnValue(false));

        $class = ($identifiable instanceof File) ? 'file' : 'folder';

        $this->setExpectedException(
            'Xi\Filelib\Authorization\AccessDeniedException',
            "{$expectedException} access to {$class} #{$identifiable->getId()} was denied"
        );

        $this->ed
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                Events::BEFORE_DENY_ACCESS,
                $this->isInstanceOf('Xi\Filelib\Event\IdentifiableEvent')
            );

        $plugin = new AuthorizationPlugin($this->adapter);
        $plugin->attachTo($this->filelib);

        if ($identifiable instanceof File) {
            $event = new FileEvent($identifiable);
        } else {
            $event = new FolderEvent($identifiable);
        }

        $plugin->$method($event);
    }

    /**
     * @test
     * @dataProvider provideData
     */
    public function grantedPermissionShouldDoNothing(
        $method,
        $expectedMethod,
        $expectedException,
        Identifiable $identifiable
    ) {
        $this->adapter->expects($this->once())->method($expectedMethod)->will($this->returnValue(true));

        $this->ed
            ->expects($this->never())
            ->method('dispatch');

        $plugin = new AuthorizationPlugin($this->adapter);
        $plugin->attachTo($this->filelib);

        if ($identifiable instanceof File) {
            $event = new FileEvent($identifiable);
        } else {
            $event = new FolderEvent($identifiable);
        }

        $plugin->$method($event);
    }




}
