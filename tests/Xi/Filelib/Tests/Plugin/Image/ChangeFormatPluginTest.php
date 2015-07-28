<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image;

use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Plugin\Image\ChangeFormatPlugin;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\Events;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Profile\FileProfile;

/**
 * @group plugin
 */
class ChangeFormatPluginTest extends TestCase
{
    /**
     * @var ChangeFormatPlugin
     */
    private $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileRepository;

    public function setUp()
    {
        parent::setUp();

        $this->fileRepository = $this
            ->getMockBuilder('Xi\Filelib\File\FileRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $filelib = $this->getMockedFilelib(null, $this->fileRepository);

        $this->plugin = new ChangeFormatPlugin(
            array()
        );

        $this->plugin->attachTo($filelib);
    }

    /**
     * @test
     */
    public function classExtendsBasePlugin()
    {
        $this->assertArrayHasKey(
            'Xi\Filelib\Plugin\BasePlugin',
            class_parents($this->plugin)
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

        $this->plugin->beforeUpload($event);
    }

    /**
     * @test
     */
    public function beforeUploadShouldReturnSameUploadWhenNotImage()
    {
        $this->plugin->setProfiles(array('tussi'));

        $upload = new FileUpload(ROOT_TESTS . '/data/refcard.pdf');

        $folder = $this->getMockedFolder();
        $profile = new FileProfile('tussi');

        $event = new FileUploadEvent($upload, $folder, $profile);

        $this->plugin->beforeUpload($event);

        $this->assertSame($upload, $event->getFileUpload());
    }

    /**
     * @test
     */
    public function beforeUploadShouldReturnNewUploadWhenImage()
    {
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $this->plugin->setProfiles(array('tussi'));

        $folder = Folder::create();

        $profile = new FileProfile('tussi');

        $event = new FileUploadEvent($upload, $folder, $profile);

        $this->plugin->beforeUpload($event);

        $xupload = $event->getFileUpload();
        $this->assertInstanceOf('Xi\Filelib\File\Upload\FileUpload', $xupload);
        $this->assertNotSame($upload, $xupload);
        $this->assertEquals('self-lussing-manatee.jpg', $xupload->getUploadFilename());
    }

    /**
     * @test
     */
    public function getSubscribedEventsShouldReturnCorrectEvents()
    {
        $events = ChangeFormatPlugin::getSubscribedEvents();
        $this->assertArrayHasKey(Events::PROFILE_AFTER_ADD, $events);
        $this->assertArrayHasKey(Events::FILE_UPLOAD, $events);
    }
}
