<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image;

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
        $filelib
            ->expects($this->once())
            ->method('getTempDir')
            ->will($this->returnValue(ROOT_TESTS . '/data/temp'));

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
        $upload = $this->getMockBuilder('Xi\Filelib\File\Upload\FileUpload')
                       ->setConstructorArgs(array(ROOT_TESTS . '/data/refcard.pdf'))
                       ->getMock();

        $this->plugin->setProfiles(array('tussi'));

        $upload->expects($this->once())->method('getMimeType')->will($this->returnValue('video/lus'));

        $folder = $this->getMockedFolder();
        $profile = $this->getMockedFileProfile();
        $profile->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('tussi'));
        $event = new FileUploadEvent($upload, $folder, $profile);

        $this->plugin->beforeUpload($event);

        $this->assertSame($upload, $event->getFileUpload());
    }

    /**
     * @test
     */
    public function beforeUploadShouldReturnNewUploadWhenImage()
    {
        $helper = $this->getMock('Xi\Filelib\Plugin\Image\ImageMagickHelper');

        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $plugin = new ChangeFormatPlugin();

        $filelib = $this->getMockedFilelib(null, $this->fileRepository);
        $filelib
            ->expects($this->once())
            ->method('getTempDir')
            ->will($this->returnValue(ROOT_TESTS . '/data/temp'));

        $plugin->attachTo($filelib);

        $plugin->setProfiles(array('tussi'));

        $folder = $this->getMockedFolder();

        $profile = new FileProfile('tussi');

        $event = new FileUploadEvent($upload, $folder, $profile);

        $plugin->beforeUpload($event);

        $xupload = $event->getFileUpload();
        $this->assertInstanceOf('Xi\Filelib\File\Upload\FileUpload', $xupload);
        $this->assertNotSame($upload, $xupload);
        $this->assertEquals('self-lussing-manatee.jpeg', $xupload->getUploadFilename());
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
