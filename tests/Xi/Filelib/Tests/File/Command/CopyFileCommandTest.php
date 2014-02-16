<?php

namespace Xi\Filelib\Tests\File\Command;

use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\Command\CopyFileCommand;
use Xi\Filelib\Events;

class CopyFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    protected $op;
    protected $folder;
    protected $ack;

    public function setUp()
    {
        $this->op = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                    ->disableOriginalConstructor()
                    ->setMethods(array('getFolderOperator', 'findByFilename', 'getBackend', 'getEventDispatcher', 'getStorage', 'createCommand', 'generateUuid'))
                    ->getMock();
        $this->folder = $this->getMock('Xi\Filelib\Folder\Folder');
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\CopyFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\CopyFileCommand'));
    }

    public function provideNames()
    {
        return array(
            array('tohtori-vesala copy.jpg', 'tohtori-vesala.jpg'),
            array('tohtori-vesala copy 2.jpg', 'tohtori-vesala copy.jpg'),
            array('tohtori-vesala copy 3.jpg', 'tohtori-vesala copy 2.jpg'),
            array('tussinlussutus losoposki tussu copy 666', 'tussinlussutus losoposki tussu copy 665'),
            array('lisko-mikko copy 563', 'lisko-mikko copy 562'),
            array('## copy', '##'),
        );
    }

    /**
     * @test
     * @dataProvider provideNames
     */
    public function getCopyNameShouldGenerateCorrectCopyName($expected, $originalName)
    {
        $file = File::create(array('name' => 'tohtori-vesala.jpg'));

        $command = new CopyFileCommand($file, $this->folder);

        $ret = $command->getCopyName($originalName);

        $this->assertEquals($expected, $ret);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function getCopyNameShouldThrowExceptionWhenItCannotResolveCopyName()
    {
        $file = File::create(array('name' => 'tohtori-vesala.jpg'));

        $command = new CopyFileCommand($file, $this->folder);

        $ret = $command->getCopyName('');

    }

    /**
     * @test
     */
    public function getImpostorShouldReturnEqualFileIfOriginalFileIsNotFoundInFolder()
    {
        $file = File::create(array('name' => 'tohtori-vesala.jpg', 'versions' => array('tussi', 'lussi')));

        $this->op->expects($this->once())->method('findByFilename')
             ->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'), $this->equalTo('tohtori-vesala.jpg'))
             ->will($this->returnValue(false));

        $command = $this->getMockBuilder('Xi\Filelib\File\Command\CopyFileCommand')
                        ->setMethods(array('handleImpostorResource'))
                        ->setConstructorArgs(array($file, $this->folder))
                        ->getMock();
        $command->attachTo($this->getMockedFilelib(null, $this->op));
        $command->expects($this->once())->method('handleImpostorResource');

        $impostor = $command->getImpostor($file);

        $this->assertCount(0, $impostor->getVersions());

        $this->assertEquals($file->getName(), $impostor->getName());
        $this->assertUuid($impostor->getUuid());
    }

    /**
     * @test
     */
    public function getImpostorShouldIterateUntilFileIsNotFoundInFolder()
    {
        $file = File::create(array('name' => 'tohtori-vesala.jpg', 'versions' => array('tussi', 'lussi')));

        $this->folder->expects($this->any())->method('getId')->will($this->returnValue(666));

        $this->op->expects($this->at(0))->method('findByFilename')
             ->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'), $this->equalTo('tohtori-vesala.jpg'))
             ->will($this->returnValue(true));

        $this->op->expects($this->at(1))->method('findByFilename')
             ->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'), $this->equalTo('tohtori-vesala copy.jpg'))
             ->will($this->returnValue(true));

        $this->op->expects($this->at(2))->method('findByFilename')
             ->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'), $this->equalTo('tohtori-vesala copy 2.jpg'))
             ->will($this->returnValue(false));

        $command = $this->getMockBuilder('Xi\Filelib\File\Command\CopyFileCommand')
                        ->setMethods(array('handleImpostorResource'))
                        ->setConstructorArgs(array($file, $this->folder))
                        ->getMock();
        $command->attachTo($this->getMockedFilelib(null, $this->op));
        $command->expects($this->once())->method('handleImpostorResource');

        $impostor = $command->getImpostor();
        $this->assertCount(0, $impostor->getVersions());

        $this->assertUuid($impostor->getUuid());

        $this->assertEquals('tohtori-vesala copy 2.jpg', $impostor->getName());
        $this->assertEquals(666, $impostor->getFolderId());

    }

    /**
     * @return array
     */
    public function provideDataForCommandExecution()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @test
     * @dataProvider provideDataForCommandExecution
     */
    public function commandShouldExecute($exclusiveResource)
    {
        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->op->expects($this->any())->method('getBackend')->will($this->returnValue($backend));
        $this->op->expects($this->any())->method('getStorage')->will($this->returnValue($storage));
        $this->op->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($eventDispatcher));

        $file = File::create(array('name' => 'tohtori-vesala.jpg', 'resource' => Resource::create(array('exclusive' => $exclusiveResource))));

        $backend
            ->expects($this->once())
            ->method('createFile')
            ->with(
                $this->isInstanceOf('Xi\Filelib\File\File')
            );

        if ($exclusiveResource) {

            $storage->expects($this->once())->method('retrieve')
                     ->with($this->isInstanceOf('Xi\Filelib\File\Resource'))
                     ->will($this->returnValue('xooxoo'));
            $storage->expects($this->once())->method('store')
                    ->with($this->isInstanceOf('Xi\Filelib\File\Resource'), $this->equalTo('xooxoo'));

            $backend->expects($this->once())->method('createResource')
                    ->with($this->isInstanceOf('Xi\Filelib\File\Resource'))
                    ->will($this->returnArgument(0));
        } else {
            $storage->expects($this->never())->method('retrieve');
            $storage->expects($this->never())->method('store');
            $backend->expects($this->never())->method('createResource');
        }

        $eventDispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::FILE_BEFORE_COPY, $this->isInstanceOf('Xi\Filelib\Event\FileCopyEvent'));

        $eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::FOLDER_BEFORE_WRITE_TO, $this->isInstanceOf('Xi\Filelib\Event\FolderEvent'));

        $eventDispatcher
            ->expects($this->at(2))
            ->method('dispatch')
            ->with(Events::FILE_AFTER_COPY, $this->isInstanceOf('Xi\Filelib\Event\FileCopyEvent'));

        $afterUploadCommand = $this
            ->getMockBuilder('Xi\Filelib\File\Command\AfterUploadFileCommand')
            ->disableOriginalConstructor()
            ->getMock();

        $this->op
            ->expects($this->any())
            ->method('createCommand')
            ->with($this->equalTo('Xi\Filelib\File\Command\AfterUploadFileCommand'))
            ->will($this->returnValue($afterUploadCommand));

        $afterUploadCommand->expects($this->once())->method('execute')->will($this->returnValue($file));

        $command = new CopyFileCommand($file, $this->folder);
        $command->attachTo($this->getMockedFilelib(null, $this->op));
        $ret = $command->execute();

        $this->assertInstanceOf('Xi\Filelib\File\File', $ret);
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $folder = $this->getMock('Xi\Filelib\Folder\Folder');
        $file = $this->getMock('Xi\Filelib\File\File');
        $uuid = Uuid::uuid4()->toString();

        $command = new CopyFileCommand($file, $folder, $uuid);

        $serialized = serialize($command);
        $command2 = unserialize($serialized);

        $this->assertAttributeEquals($file, 'file', $command2);
        $this->assertAttributeEquals($folder, 'folder', $command2);
        $this->assertAttributeEquals($uuid, 'uuid', $command2);
    }

    /**
     * @test
     */
    public function respectsPresetUuid()
    {
        $folder = Folder::create(array('id' => 123));
        $file = File::create(array('id' => 321));

        $command = new CopyFileCommand($file, $folder);
        $this->assertUuid($command->getUuid());

        $presetCommand = new CopyFileCommand($file, $folder, 'lussen-tussen-hof');
        $this->assertSame('lussen-tussen-hof', $presetCommand->getUuid());
    }

    /**
     * @test
     */
    public function topicIsCorrect()
    {
        $command = $this->getMockBuilder('Xi\Filelib\File\Command\CopyFileCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();

        $this->assertEquals('xi_filelib.command.file.copy', $command->getTopic());
    }
}
