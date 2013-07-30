<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image;

use Imagick;
use Xi\Filelib\Plugin\Image\ChangeFormatPlugin;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\Events;

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
    private $fileOperator;

    public function setUp()
    {
        parent::setUp();

        $this->fileOperator = $this
            ->getMockBuilder('Xi\Filelib\File\FileOperator')
            ->disableOriginalConstructor()
            ->getMock();

        $filelib = $this->getMockedFilelib(null, $this->fileOperator);
        $filelib
            ->expects($this->once())
            ->method('getTempDir')
            ->will($this->returnValue(ROOT_TESTS . '/data/temp'));

        $this->plugin = new ChangeFormatPlugin(
            'lus',
            array()
        );

        $this->plugin->attachTo($filelib);
    }

    /**
     * @test
     */
    public function classExtendsAbstractPlugin()
    {
        $this->assertArrayHasKey(
            'Xi\Filelib\Plugin\AbstractPlugin',
            class_parents($this->plugin)
        );
    }

    /**
     * @test
     */
    public function gettersShouldWorkAsExpected()
    {
        $this->assertEquals('lus', $this->plugin->getTargetExtension());
    }

    /**
     * @test
     */
    public function getImageMagickHelperShouldReturnImageMagickHelper()
    {
        $helper = $this->plugin->getImageMagickHelper();
        $this->assertInstanceOf('Xi\Filelib\Plugin\Image\ImageMagickHelper', $helper);
        $this->assertSame($helper, $this->plugin->getImageMagickHelper());
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

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');
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

        $mock = $this->getMock('Imagick');
        $mock->expects($this->once())
             ->method('writeImage')
             ->with($this->matchesRegularExpression('#^' . ROOT_TESTS . '/data/temp#'));

        $helper->expects($this->once())->method('createImagick')->will($this->returnValue($mock));

        $helper->expects($this->once())->method('execute')->with($this->equalTo($mock));

        $upload = $this->getMockBuilder('Xi\Filelib\File\Upload\FileUpload')
                       ->setConstructorArgs(array(ROOT_TESTS . '/data/self-lussing-manatee.jpg'))
                       ->getMock();

        $upload->expects($this->any())->method('getMimeType')->will($this->returnValue('image/jpeg'));
        $upload->expects($this->atLeastOnce())->method('getUploadFilename')->will($this->returnValue('self-lussing-manatee.jpg'));

        $nupload = $this->getMockBuilder('Xi\Filelib\File\Upload\FileUpload')
                       ->setConstructorArgs(array(ROOT_TESTS . '/data/self-lussing-manatee.jpg'))
                       ->getMock();
        $nupload->expects($this->once())->method('setTemporary')->with($this->equalTo(true));
        $nupload->expects($this->once())->method('setOverrideFilename')->with($this->equalTo('self-lussing-manatee.lus'));

        $this->fileOperator
             ->expects($this->once())
             ->method('prepareUpload')
             ->with($this->matchesRegularExpression('#^' . ROOT_TESTS . '/data/temp#'))
             ->will($this->returnValue($nupload));

        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\Image\ChangeFormatPlugin')
                       ->setMethods(array('getImageMagickHelper'))
                       ->setConstructorArgs(array(
                           'lus'
                       ))
                       ->getMock();

        $filelib = $this->getMockedFilelib(null, $this->fileOperator);
        $filelib
            ->expects($this->once())
            ->method('getTempDir')
            ->will($this->returnValue(ROOT_TESTS . '/data/temp'));

        $plugin->attachTo($filelib);

        $plugin->setProfiles(array('tussi'));

        $plugin->expects($this->any())->method('getImageMagickHelper')->will($this->returnValue($helper));

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');
        $profile = $this->getMockedFileProfile();
        $profile->expects($this->atLeastOnce())->method('getIdentifier')->will($this->returnValue('tussi'));
        $event = new FileUploadEvent($upload, $folder, $profile);

        $plugin->beforeUpload($event);

        $xupload = $event->getFileUpload();

        $this->assertInstanceOf('Xi\Filelib\File\Upload\FileUpload', $xupload);
        $this->assertNotSame($upload, $xupload);
    }

    /**
     * @test
     */
    public function getSubscribedEventsShouldReturnCorrectEvents()
    {
        $events = ChangeFormatPlugin::getSubscribedEvents();
        $this->assertArrayHasKey(Events::PROFILE_AFTER_ADD, $events);
        $this->assertArrayHasKey(Events::FILE_BEFORE_CREATE, $events);
    }
}
