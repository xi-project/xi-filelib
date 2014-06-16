<?php

namespace Xi\Filelib\Tests\Plugin;

use Xi\Filelib\Plugin\PluginManager;
use Xi\Filelib\File\File;
use Xi\Filelib\Events;
use Xi\Filelib\Plugin\RandomizeNamePlugin;

class PluginManagerTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $ed;

    /**
     * @var PluginManager
     */
    private $manager;

    public function setUp()
    {
        $this->ed = $this->getMockedEventDispatcher();

        $this->manager = new PluginManager(
            $this->ed
        );
    }

    /**
     * @test
     */
    public function addsPluginToSpecificProfiles()
    {
        $plugin = new RandomizeNamePlugin();

        $this->ed->expects($this->once())->method('addSubscriber')->with($plugin);

        $this->ed
            ->expects($this->once())
            ->method('dispatch')->with(
                Events::PLUGIN_AFTER_ADD,
                $this->isInstanceOf('Xi\Filelib\Event\PluginEvent')
            );

        $this->manager->addPlugin($plugin, array('sucklee', 'ducklee'));
        $this->assertTrue($plugin->belongsToProfile('sucklee'));
        $this->assertFalse($plugin->belongsToProfile('suckler'));
    }

    /**
     * @test
     */
    public function addsPluginToAllProfiles()
    {
        $plugin = new RandomizeNamePlugin();

        $this->ed->expects($this->once())->method('addSubscriber')->with($plugin);

        $this->ed
            ->expects($this->once())
            ->method('dispatch')->with(
                Events::PLUGIN_AFTER_ADD,
                $this->isInstanceOf('Xi\Filelib\Event\PluginEvent')
            );

        $this->manager->addPlugin($plugin);
        $this->assertTrue($plugin->belongsToProfile('sucklee'));
        $this->assertTrue($plugin->belongsToProfile('suckler'));
    }

    /**
     * @test
     */
    public function acceptsPresetName()
    {
        $plugin = new RandomizeNamePlugin();

        $this->manager->addPlugin($plugin, array(), 'tenhusen-suuruuden-ylistyksen-plugin');
        $this->assertSame($plugin, $this->manager->getPlugin('tenhusen-suuruuden-ylistyksen-plugin'));
    }

    /**
     * @test
     */
    public function cannotAddTwoPluginsWithSameName()
    {
        $this->ed
            ->expects($this->once())
            ->method('dispatch')->with(
                Events::PLUGIN_AFTER_ADD,
                $this->isInstanceOf('Xi\Filelib\Event\PluginEvent')
            );

        $plugin = new RandomizeNamePlugin();
        $plugin2 = new RandomizeNamePlugin();

        $this->manager->addPlugin($plugin, array(), 'tenhusen-suuruuden-ylistyksen-plugin');

        $this->setExpectedException('Xi\Filelib\InvalidArgumentException');
        $this->manager->addPlugin($plugin2, array(), 'tenhusen-suuruuden-ylistyksen-plugin');
    }

    /**
     * @test
     */
    public function generatesSanePluginName()
    {
        $plugin = new RandomizeNamePlugin();
        $plugin2 = new RandomizeNamePlugin();
        $this->manager->addPlugin($plugin);
        $this->manager->addPlugin($plugin2);

        $plugins = $this->manager->getPlugins();
        $this->assertCount(2, $plugins);
    }

    /**
     * @test
     */
    public function throwsUpWithNonExistingPlugin()
    {
        $this->setExpectedException('Xi\Filelib\InvalidArgumentException');
        return $this->manager->getPlugin('tenhusen-ylistyksen-suuruuden-plugari');
    }

}
