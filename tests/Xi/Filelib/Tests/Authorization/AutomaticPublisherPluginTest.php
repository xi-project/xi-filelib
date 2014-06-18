<?php

namespace Xi\Filelib\Tests\Authorization;

use Xi\Filelib\Authorization\AutomaticPublisherPlugin;
use Xi\Filelib\Events;
use Xi\Filelib\Event\FileEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Xi\Filelib\File\File;

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
        $this->file = $this->getMockedFile();
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
                Events::FILE_AFTER_AFTERUPLOAD => 'doPublish',
                Events::FILE_BEFORE_UPDATE => 'doUnpublish',
                Events::FILE_AFTER_UPDATE => 'doPublish',
                Events::PROFILE_AFTER_ADD => 'onFileProfileAdd'
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
            ->expects($this->once())
            ->method('publishAllVersions')
            ->with($this->file);

        $event = new FileEvent($this->file);
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
            ->method('publishAllVersions');

        $event = new FileEvent($this->file);
        $this->plugin->doPublish($event);
    }


    /**
     * @test
     */
    public function pluginShouldAutomaticallyUnpublishIfPublished()
    {
        $this->publisher
            ->expects($this->once())
            ->method('unpublishAllVersions')
            ->with($this->file);

        $event = new FileEvent($this->file);
        $this->plugin->doUnpublish($event);
    }

    /**
     * @test
     */
    public function pluginShouldNotPublishRecursively()
    {
        $this->adapter
            ->expects($this->once())
            ->method('isFileReadableByAnonymous')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue(true));

        $ed = new EventDispatcher();
        $ed->addSubscriber($this->plugin);

        $file = $this->file;

        $this->publisher
            ->expects($this->once())
            ->method('publishAllVersions')
            ->with($this->file)
            ->will(
                $this->returnCallback(
                    function () use ($ed, $file) {
                        $ed->dispatch(Events::FILE_AFTER_UPDATE, new FileEvent($file));
                    }
                )
            );

        $event = new FileEvent($this->file);
        $this->plugin->doPublish($event);
    }

    /**
     * @test
     */
    public function pluginShouldNotUnpublishRecursively()
    {
        $this->adapter
            ->expects($this->never())
            ->method('isFileReadableByAnonymous');

        $ed = new EventDispatcher();
        $ed->addSubscriber($this->plugin);

        $file = File::create(array('data' => array('publisher.published' => 1)));

        $this->publisher
            ->expects($this->once())
            ->method('unpublishAllVersions')
            ->with($file)
            ->will(
                $this->returnCallback(
                    function () use ($ed, $file) {
                        $ed->dispatch(Events::FILE_BEFORE_UPDATE, new FileEvent($file));
                    }
                )
            );

        $event = new FileEvent($file);
        $this->plugin->doUnpublish($event);
    }
}
