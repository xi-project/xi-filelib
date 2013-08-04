<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\Authorization\AutomaticPublisherPlugin;
use Xi\Filelib\Events;
use Xi\Filelib\Event\FileEvent;

class AutomaticPublisherPluginTest extends \Xi\Filelib\Tests\TestCase
{
    private $publisher;

    /**
     * @var AutomaticPublisherPlugin
     */
    private $plugin;

    private $file;

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
                Events::FILE_BEFORE_DELETE => 'doUnpublish',
                Events::FILE_BEFORE_UPDATE => 'doUnpublishAndPublish',
                Events::PROFILE_AFTER_ADD => 'onFileProfileAdd'
            ),
            AutomaticPublisherPlugin::getSubscribedEvents()
        );
    }

    /**
     * @test
     */
    public function pluginShouldAutomaticallyPublishIfWorldReadable()
    {
        $this->publisher
            ->expects($this->once())
            ->method('publish')
            ->with($this->file);

        $event = new FileEvent($this->file);
        $this->plugin->doPublish($event);
    }

    /**
     * @test
     */
    public function pluginShouldAutomaticallyUnpublishIfPublished()
    {
        $this->expectFilePublished(true);

        $this->publisher
            ->expects($this->once())
            ->method('unpublish')
            ->with($this->file);

        $event = new FileEvent($this->file);
        $this->plugin->doUnpublish($event);
    }

    /**
     * @test
     */
    public function pluginShouldNotAutomaticallyUnpublishIfNotPublished()
    {
        $this->expectFilePublished(false);

        $this->publisher
            ->expects($this->never())
            ->method('unpublish');

        $event = new FileEvent($this->file);
        $this->plugin->doUnpublish($event);
    }

    /**
     * @test
     */
    public function pluginShouldUnpublishAndPublishOnUpdate()
    {
        $this->expectFilePublished(true);

        $this->publisher
            ->expects($this->at(0))
            ->method('unpublish')
            ->with($this->file);

        $this->publisher
            ->expects($this->at(1))
            ->method('publish')
            ->with($this->file);

        $event = new FileEvent($this->file);
        $this->plugin->doUnPublishAndPublish($event);
    }

    private function expectFilePublished($ret)
    {
        $this->publisher
            ->expects($this->any())
            ->method('isPublished')
            ->with($this->file)
            ->will($this->returnValue($ret));
    }

}
