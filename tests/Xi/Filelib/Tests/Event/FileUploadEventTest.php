<?php

namespace Xi\Filelib\Tests\Event;

use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\File\Upload\FileUpload;

class FileUploadEventTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Event\FileUploadEvent'));
        $this->assertContains(
            'Symfony\Component\EventDispatcher\Event',
            class_parents('Xi\Filelib\Event\FileUploadEvent')
        );
    }

    /**
     * @test
     */
    public function eventShouldInitializeCorrectly()
    {
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $profile = $this->getMockedFileProfile();
        $folder = $this->getMockedFolder();
        $event = new FileUploadEvent($upload, $folder, $profile);

        $this->assertSame($upload, $event->getFileUpload());
        $this->assertSame($folder, $event->getFolder());
        $this->assertSame($profile, $event->getProfile());
    }

    /**
     * @test
     */
    public function fileUploadShouldBeReplacable()
    {
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $folder = $this->getMockedFolder();
        $profile = $this->getMockedFileProfile();
        $event = new FileUploadEvent($upload, $folder, $profile);

        $upload2 = $event->getFileUpload();
        $this->assertSame($upload, $upload2);

        $upload3 = new FileUpload(ROOT_TESTS . '/data/refcard.pdf');
        $event->setFileUpload($upload3);
        $upload4 = $event->getFileUpload();
        $this->assertSame($upload3, $upload4);
    }
}
