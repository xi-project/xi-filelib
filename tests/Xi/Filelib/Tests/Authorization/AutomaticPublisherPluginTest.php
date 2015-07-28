<?php

namespace Xi\Filelib\Tests\Authorization;

use Xi\Filelib\Authorization\AutomaticPublisherPlugin;
use Xi\Filelib\Event\VersionProviderEvent;
use Xi\Filelib\Events as CoreEvents;
use Xi\Filelib\Plugin\VersionProvider\Events;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\File\File;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Versionable\Version;

class AutomaticPublisherPluginTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $publisher;

    /**
     * @var AutomaticPublisherPlugin
     */
    private $plugin;

    private $file;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    public function setUp()
    {
        $this->publisher = $this->getMockedPublisher();
        $this->adapter = $this->getMock('Xi\Filelib\Authorization\AuthorizationAdapter');

        $this->plugin = new AutomaticPublisherPlugin($this->publisher, $this->adapter);
        $this->file = File::create(
            [
                'data' => [
                    'versions' => ['tusso', 'lusso', 'xusso']
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Authorization\AutomaticPublisherPlugin');
    }

    /**
     * @test
     */
    public function pluginShouldSubscribeToCorrectEvents()
    {
        $this->assertEquals(
            array(
                CoreEvents::FILE_AFTER_UPDATE => 'doPermissionsCheck',
                CoreEvents::PROFILE_AFTER_ADD => 'onFileProfileAdd',
                Events::VERSIONS_UNPROVIDED => 'doUnpublish',
                Events::VERSIONS_PROVIDED => 'doPublish',
            ),
            AutomaticPublisherPlugin::getSubscribedEvents()
        );
    }

    /**
     * @test
     */
    public function pluginShouldPublishIfWorldReadable()
    {
        $this->adapter
            ->expects($this->once())
            ->method('isFileReadableByAnonymous')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue(true));

        $this->publisher
            ->expects($this->exactly(2))
            ->method('publishVersion')
            ->with($this->file, $this->isInstanceOf(Version::class));

        $vp = $this->getMockedVersionProvider(array('lusso', 'con-tusso'));

        $event = new VersionProviderEvent($vp, $this->file, array('lusso', 'con-tusso'));
        $this->plugin->doPublish($event);
    }

    /**
     * @test
     */
    public function publishExitsEarlyIfResource()
    {
        $this->adapter
            ->expects($this->never())
            ->method('isFileReadableByAnonymous');

        $this->publisher
            ->expects($this->never())
            ->method('publishVersion');

        $vp = $this->getMockedVersionProvider(array('lusso', 'con-tusso'));
        $event = new VersionProviderEvent($vp, Resource::create(), array('lusso', 'con-tusso'));
        $this->plugin->doPublish($event);
    }

    /**
     * @test
     */
    public function pluginShouldNotPublishIfNotWorldReadable()
    {
        $this->adapter
            ->expects($this->once())
            ->method('isFileReadableByAnonymous')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue(false));

        $this->publisher
            ->expects($this->never())
            ->method('publishVersion');

        $vp = $this->getMockedVersionProvider(array('lusso', 'con-tusso'));
        $event = new VersionProviderEvent($vp, $this->file, array('lusso', 'con-tusso'));
        $this->plugin->doPublish($event);
    }


    /**
     * @test
     */
    public function pluginShouldAutomaticallyUnpublishIfPublished()
    {
        $this->publisher
            ->expects($this->exactly(3))
            ->method('unpublishVersion')
            ->with($this->file, $this->isInstanceOf(Version::class));

        $vp = $this->getMockedVersionProvider(array('lusso', 'con-tusso'));
        $event = new VersionProviderEvent($vp, $this->file, array('lusso', 'con-tusso', 'loso'));
        $this->plugin->doUnpublish($event);
    }

    /**
     * @test
     */
    public function unpublishExitsEarlyIfResource()
    {
        $this->publisher
            ->expects($this->never())
            ->method('unpublishVersion');

        $vp = $this->getMockedVersionProvider(array('lusso', 'con-tusso'));
        $event = new VersionProviderEvent($vp, Resource::create(), array('lusso', 'con-tusso', 'loso'));
        $this->plugin->doUnpublish($event);
    }


    /**
     * @test
     */
    public function unpublishesOnRemovedAuthorization()
    {
        $this->adapter
            ->expects($this->once())
            ->method('isFileReadableByAnonymous')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue(false));

        $this->publisher
            ->expects($this->once())
            ->method('unpublishAllVersions')
            ->with($this->file);

        $event = new FileEvent($this->file);
        $this->plugin->doPermissionsCheck($event);
    }

    /**
     * @test
     */
    public function doesNotUnpublisOnUnchangedAuthorization()
    {
        $this->adapter
            ->expects($this->once())
            ->method('isFileReadableByAnonymous')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue(true));

        $this->publisher
            ->expects($this->never())
            ->method('unpublishAllVersions');

        $event = new FileEvent($this->file);
        $this->plugin->doPermissionsCheck($event);
    }

}
