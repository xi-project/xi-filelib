<?php

namespace Xi\Filelib\Tests\File\Command;

use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\File\File;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Command\UploadFileCommand;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Backend\Finder\ResourceFinder;
use ArrayIterator;
use Xi\Filelib\Events;

class UploadFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\UploadFileCommand'));
        $this->assertContains('Xi\Filelib\Command\Command', class_implements('Xi\Filelib\File\Command\UploadFileCommand'));
    }

    public function provideDataForUploadTest()
    {
        return array(
            array(false, false),
            array(true, true),
        );
    }

    /**
     * @test
     * @dataProvider provideDataForUploadTest
     */
    public function commandShouldUploadAndDelegateCorrectly($expectedCallToPublish, $readableByAnonymous)
    {
        $dispatcher = $this->getMockedEventDispatcher();

        $file = $this->getMockedFile();

        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::FOLDER_BEFORE_WRITE_TO, $this->isInstanceOf('Xi\Filelib\Event\FolderEvent'));

        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::FILE_BEFORE_CREATE, $this->isInstanceOf('Xi\Filelib\Event\FileUploadEvent'));

        $dispatcher
            ->expects($this->at(2))
            ->method('dispatch')
            ->with(Events::FILE_AFTER_CREATE, $this->isInstanceOf('Xi\Filelib\Event\FileEvent'));

        $folder = Folder::create(array('id' => 1));
        $path = ROOT_TESTS . '/data/self-lussing-manatee.jpeg';

        $backend = $this->getMockedBackend();
        $backend
            ->expects($this->once())
            ->method('createFile')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'));

        $storage = $this->getMockedStorage();

        $op = $this->getMockedFileRepository();
        $pm = $this->getMockedProfileManager(array('versioned'));

        $afterUploadCommand = $this->getMockedExecutable();
        $afterUploadCommand
            ->expects($this->once())->method('execute')
            ->will($this->returnValue($file));

        $op->expects($this->once())
           ->method('createExecutable')
           ->with(
                $this->equalTo('Xi\Filelib\File\Command\AfterUploadFileCommand'),
                $this->isType('array')
            )
           ->will($this->returnValue($afterUploadCommand));

        $command = $this->getMockBuilder('Xi\Filelib\File\Command\UploadFileCommand')
                        ->setConstructorArgs(array(new FileUpload($path), $folder, 'versioned'))
                        ->setMethods(array('getResource'))
                        ->getMock();

        $rere = $this->getMockedResourceRepository();

        $filelib = $this->getMockedFilelib(
            null,
            array(
                'fire' => $op,
                'storage' => $storage,
                'ed' => $dispatcher,
                'backend' => $backend,
                'pm' => $pm,
                'rere' => $rere,
            )
        );

        $command->attachTo($filelib);

        $rere
            ->expects($this->once())->method('findResourceForUpload')
            ->with(
                $this->isInstanceOf('Xi\Filelib\File\File'),
                $this->isInstanceOf('Xi\Filelib\File\Upload\FileUpload')
            )
            ->will($this->returnValue(Resource::create()));

        $ret = $command->execute();
        $this->assertInstanceOf('Xi\Filelib\File\File', $ret);
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $upload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $folder = $this->getMockedFolder();
        $profile = 'lussenhof';
        $uuid = Uuid::uuid4()->toString();

        $command = new UploadFileCommand($upload, $folder, $profile);
        $command->setUuid($uuid);

        $serialized = serialize($command);

        $command2 = unserialize($serialized);

        $this->assertAttributeEquals($folder, 'folder', $command2);
        $this->assertAttributeEquals($profile, 'profile', $command2);
        $this->assertAttributeInstanceof('Xi\Filelib\File\Upload\FileUpload', 'upload', $command2);
        $this->assertAttributeEquals($uuid, 'uuid', $command2);
    }


    /**
     * @test
     */
    public function respectsPresetUuid()
    {
        $folder = Folder::create(array('id' => 123));
        $fileupload = new FileUpload(ROOT_TESTS . '/data/self-lussing-manatee.jpeg');

        $command = new UploadFileCommand($fileupload, $folder, 'oh-the-huge-manatee');
        $this->assertUuid($command->getUuid());

        $presetCommand = new UploadFileCommand($fileupload, $folder, 'oh-the-huge-manatee');
        $presetCommand->setUuid('lussen-meister-hof');

        $this->assertSame('lussen-meister-hof', $presetCommand->getUuid());
    }

    /**
     * @test
     */
    public function topicIsCorrect()
    {
        $command = $this->getMockBuilder('Xi\Filelib\File\Command\UploadFileCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();

        $this->assertEquals('xi_filelib.command.file.upload', $command->getTopic());
    }
}
