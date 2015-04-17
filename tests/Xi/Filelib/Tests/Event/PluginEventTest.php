<?php

namespace Xi\Filelib\Tests\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\Event\PluginEvent;

class PluginEventTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Event\PluginEvent'));
        $this->assertContains(
            'Symfony\Component\EventDispatcher\Event',
            class_parents('Xi\Filelib\Event\PluginEvent')
        );
    }

    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $plugin = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $filelib = $this->getMockedFilelib();

        $event = new PluginEvent($plugin, $filelib);

        $plugin2 = $event->getPlugin();
        $this->assertSame($plugin, $plugin2);

        $this->assertSame($filelib, $event->getFilelib());
    }
}
