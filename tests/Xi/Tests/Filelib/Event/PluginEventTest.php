<?php

namespace Xi\Tests\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\Event\PluginEvent;

class PluginEventTest extends \Xi\Tests\Filelib\TestCase
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
        $event = new PluginEvent($plugin);

        $plugin2 = $event->getPlugin();
        $this->assertSame($plugin, $plugin2);
    }
}
