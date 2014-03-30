<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin;

use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Plugin\RandomizeNamePlugin;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\Events;

/**
 * @group plugin
 */
class RandomizeNamePluginTest extends TestCase
{
    /**
     * @test
     */
    public function gettersShouldWorkAsExpected()
    {
        $plugin = new RandomizeNamePlugin('xooxers');
        $this->assertEquals('xooxers', $plugin->getPrefix());
    }

    public function provideOverrideFilenames()
    {
        return array(
            array('tussi', 'tussenhof'),
            array('lus', 'tussenhof'),
            array('k_makkara', 'tussenhof'),
        );
    }

    /**
     * @test
     */
    public function beforeUploadShouldExitEarlyIfPluginDoesntHaveProfile()
    {
        $profile = $this->getMockedFileProfile();

        $event = $this->getMockBuilder('Xi\Filelib\Event\FileUploadEvent')
                      ->disableOriginalConstructor()
                      ->getMock();

        $event->expects($this->once())->method('getProfile')->will($this->returnValue($profile));

        $event->expects($this->never())->method('getFileUpload');

        $plugin = new RandomizeNamePlugin();

        $plugin->beforeUpload($event);
    }

    /**
     * @test
     */
    public function beforeUploadShouldRandomizeUploadFilename()
    {
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $folder = $this->getMockedFolder();
        $profile = $this->getMockedFileProfile();
        $profile->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('tussi'));
        $event = new FileUploadEvent($upload, $folder, $profile);

        $plugin = new RandomizeNamePlugin();
        $plugin->setProfiles(array('tussi'));

        $plugin->beforeUpload($event);

        $upload2 = $event->getFileUpload();

        $this->assertSame($upload, $upload2);

        $this->assertNotEquals('self-lussing-manatee', $upload2->getUploadFilename());

        $pinfo = pathinfo($upload2->getUploadFilename());

        $this->assertArrayHasKey('extension', $pinfo);

        $this->assertEquals('jpg', $pinfo['extension']);

        $this->assertEquals(27, strlen($upload2->getUploadFilename()));
    }

    /**
     * @test
     */
    public function beforeUploadShouldRandomizeOverriddenUploadFilename()
    {
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $upload->setOverrideFilename('tussinlussuttajankabaali');

        $folder = $this->getMockedFolder();
        $profile = $this->getMockedFileProfile();
        $profile->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('tussi'));
        $event = new FileUploadEvent($upload, $folder, $profile);

        $plugin = new RandomizeNamePlugin();
        $plugin->setProfiles(array('tussi'));

        $plugin->beforeUpload($event);

        $upload2 = $event->getFileUpload();

        $this->assertEquals($upload, $upload2);

        $this->assertNotEquals('self-lussing-manatee', $upload2->getUploadFilename());

        $pinfo = pathinfo($upload2->getUploadFilename());

        $this->assertArrayNotHasKey('extension', $pinfo);
        $this->assertEquals(23, strlen($upload2->getUploadFilename()));
    }

    public function providePrefixes()
    {
        return array(
            array('tussi'),
            array('helistin'),
            array('bansku'),
            array('johtaja'),
        );
    }

    /**
     * @test
     * @dataProvider providePrefixes
     */
    public function beforeUploadShouldPrefixRandomizedName($prefix)
    {
        $plugin = new RandomizeNamePlugin($prefix);
        $plugin->attachTo($this->getMockedFilelib());

        $plugin->setProfiles(array('tussi'));

        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $folder = $this->getMockedFolder();
        $profile = $this->getMockedFileProfile();
        $profile->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('tussi'));
        $event = new FileUploadEvent($upload, $folder, $profile);

        $plugin->beforeUpload($event);

        $upload2 = $event->getFileUpload();

        $this->assertStringStartsWith($prefix, $upload2->getUploadFilename());
        $this->assertEquals(27 + strlen($prefix), strlen($upload2->getUploadFilename()));
    }

    /**
     * @test
     */
    public function getSubscribedEventsShouldReturnCorrectEvents()
    {
        $events = RandomizeNamePlugin::getSubscribedEvents();
        $this->assertArrayHasKey(Events::PROFILE_AFTER_ADD, $events);
        $this->assertArrayHasKey(Events::FILE_BEFORE_CREATE, $events);
    }
}
