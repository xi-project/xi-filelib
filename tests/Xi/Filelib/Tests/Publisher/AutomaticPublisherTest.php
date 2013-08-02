<?php

namespace Xi\Filelib\Tests\Renderer;

use Xi\Filelib\Publisher\AutomaticPublisherPlugin;
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

    public function setUp()
    {
        $this->publisher = $this->getMockedPublisher();
        $this->plugin = new AutomaticPublisherPlugin($this->publisher);
        $this->file = $this->getMockedFile();
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Publisher\AutomaticPublisherPlugin');
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
                Events::PROFILE_AFTER_ADD => 'onFileProfileAdd'
            ),
            AutomaticPublisherPlugin::getSubscribedEvents()
        );
    }


    /**
     * @test
     */
    public function pluginShouldAutomaticallyPublish()
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
    public function pluginShouldAutomaticallyUnpublish()
    {
        $this->publisher
            ->expects($this->once())
            ->method('unpublish')
            ->with($this->file);

        $event = new FileEvent($this->file);
        $this->plugin->doUnpublish($event);
    }
}
