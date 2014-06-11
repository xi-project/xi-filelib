<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin;

use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\File\File;
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
    public function nameShouldRemainTheSameIfPluginDoesntHaveProfile()
    {
        $file = File::create(
            array(
                'name' => 'ankka-sanoo-kvaak.lus',
                'profile' => 'helistin',
            )
        );

        $profile = $this->getMockedFileProfile('xooxer');
        $event = new FileEvent($file);

        $plugin = new RandomizeNamePlugin();
        $plugin->beforeCreate($event);
        $this->assertEquals('ankka-sanoo-kvaak.lus', $file->getName());
    }

    public function provideFilenames()
    {
        return array(
            array('tenhunen-lipaisee', ''),
            array('tenhunen-imaisee-ankkaa.kvaak', 'kvaak')
        );
    }

    /**
     * @test
     * @dataProvider provideFilenames
     */
    public function beforeUploadShouldRandomizeUploadFilename($name, $expectedExtension)
    {
        $file = File::create(
            array(
                'name' => $name,
                'profile' => 'tussi'
            )
        );

        $event = new FileEvent($file);

        $plugin = new RandomizeNamePlugin();
        $plugin->setProfiles(array('tussi'));

        $plugin->beforeCreate($event);

        $this->assertNotEquals($name, $file->getName());

        $pinfo = pathinfo($file->getName());
        $this->assertUuid($pinfo['basename']);

        if ($expectedExtension) {
            $this->assertEquals($expectedExtension, $pinfo['extension']);
        } else {
            $this->assertArrayNotHasKey('extension', $pinfo);
        }
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
