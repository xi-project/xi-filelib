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
use Xi\Filelib\Events;

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
    public function belongsToProfileShouldReturnWhetherPluginBelongsToAProfile()
    {
        $plugin = $this
            ->getMockBuilder('Xi\Filelib\Plugin\AbstractPlugin')
            ->setMethods(array())
            ->getMockForAbstractClass();
        $plugin->attachTo($this->getMockedFilelib());

        $plugin->setBelongsToProfileResolver(
            function ($profile) {
                return (bool) in_array($profile, array('lussi', 'tussi'));
            }
        );

        $this->assertFalse($plugin->belongsToProfile('xoo'));
        $this->assertTrue($plugin->belongsToProfile('lussi'));
        $this->assertTrue($plugin->belongsToProfile('tussi'));
        $this->assertFalse($plugin->belongsToProfile('meisterhof'));
    }

    /**
     * @test
     */
    public function setProfileShouldBeAShortCutToSetBelongsToProfileResolver()
    {
        $plugin = $this
            ->getMockBuilder('Xi\Filelib\Plugin\AbstractPlugin')
            ->setMethods(array())
            ->getMockForAbstractClass();
        $plugin->attachTo($this->getMockedFilelib());

        $plugin->setProfiles(array('tussi', 'watussi'));

        $this->assertTrue($plugin->belongsToProfile('tussi'));
        $this->assertFalse($plugin->belongsToProfile('laamantiini'));
        $this->assertTrue($plugin->belongsToProfile('watussi'));
    }

    /**
     * @test
     */
    public function setProfileResolverExpectsCallable()
    {
        $this->setExpectedException('Xi\Filelib\InvalidArgumentException');

        $plugin = $this
            ->getMockBuilder('Xi\Filelib\Plugin\AbstractPlugin')
            ->setMethods(array())
            ->getMockForAbstractClass();

        $plugin->setBelongsToProfileResolver('libaiseb le tussi');
    }

    /**
     * @test
     */
    public function getSubscribedEventsShouldContainProfileAddEvent()
    {
        $events = AbstractPlugin::getSubscribedEvents();
        $this->assertArrayHasKey(Events::PROFILE_AFTER_ADD, $events);
    }

    /**
     * @test
     */
    public function onFileProfileAddShouldAddPluginToProfileIfPluginHasProfile()
    {
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\AbstractPlugin')
                       ->setMethods(array())
                       ->getMockForAbstractClass();
        $plugin->setProfiles(array('lussen', 'hofer'));

        $profile = $this->getMockedFileProfile();
        $profile->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('lussen'));
        $profile->expects($this->once())->method('addPlugin')->with($this->equalTo($plugin));

        $event = new FileProfileEvent($profile);
        $plugin->onFileProfileAdd($event);
    }

    /**
     * @test
     */
    public function onFileProfileAddShouldNotAddPluginToProfileIfPluginDoesNotHaveProfile()
    {
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\AbstractPlugin')
                       ->setMethods(array())
                       ->getMockForAbstractClass();
        $plugin->setProfiles(array('lussen', 'hofer'));

        $profile = $this->getMockedFileProfile();
        $profile->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('lussentussen'));
        $profile->expects($this->never())->method('addPlugin');

        $event = new FileProfileEvent($profile);
        $plugin->onFileProfileAdd($event);
    }
}
