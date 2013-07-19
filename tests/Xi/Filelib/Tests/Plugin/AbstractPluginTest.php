<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin;

use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Event\FileProfileEvent;

/**
 * @group plugin
 */
class AbstractPluginTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Plugin\AbstractPlugin'));
        $this->assertContains('Xi\Filelib\Plugin\Plugin', class_implements('Xi\Filelib\Plugin\AbstractPlugin'));
    }

    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\AbstractPlugin')->setMethods(array())->getMockForAbstractClass();

        $this->assertEquals(array(), $plugin->getProfiles());

        $profiles = array('tussin', 'lussutus');

        $this->assertSame($plugin, $plugin->setProfiles($profiles));
        $this->assertSame($profiles, $plugin->getProfiles());
    }

    /**
     * @test
     */
    public function hasProfileShouldReturnWhetherPluginBelongsToAProfile()
    {
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\AbstractPlugin')->setMethods(array())->getMockForAbstractClass();

        $plugin->setProfiles(array('lussi', 'tussi'));

        $this->assertFalse($plugin->hasProfile('xoo'));
        $this->assertTrue($plugin->hasProfile('lussi'));
        $this->assertTrue($plugin->hasProfile('tussi'));
        $this->assertFalse($plugin->hasProfile('meisterhof'));
    }

    /**
     * @test
     */
    public function getSubscribedEventsShouldReturnEmptyArray()
    {
        $events = AbstractPlugin::getSubscribedEvents();
        $this->assertArrayHasKey('xi_filelib.profile.add', $events);
    }

    /**
     * @test
     */
    public function onFileProfileAddShouldAddPluginToProfileIfPluginHasProfile()
    {
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\AbstractPlugin')
                       ->setMethods(array('getProfiles'))
                       ->getMock();

        $profile = $this->getMockedFileProfile();
        $profile->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('lussen'));
        $profile->expects($this->once())->method('addPlugin')->with($this->equalTo($plugin));

        $plugin->expects($this->atLeastOnce())->method('getProfiles')->will($this->returnValue(array('lussen', 'hofer')));

        $event = new FileProfileEvent($profile);

        $plugin->onFileProfileAdd($event);
    }

    /**
     * @test
     */
    public function onFileProfileAddShouldNotAddPluginToProfileIfPluginDoesNotHaveProfile()
    {
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\AbstractPlugin')
                       ->setMethods(array('getProfiles'))
                       ->getMock();

        $profile = $this->getMockedFileProfile();
        $profile->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('lussentussen'));
        $profile->expects($this->never())->method('addPlugin');

        $plugin->expects($this->atLeastOnce())->method('getProfiles')->will($this->returnValue(array('lussen', 'hofer')));

        $event = new FileProfileEvent($profile);
        $plugin->onFileProfileAdd($event);
    }
}
