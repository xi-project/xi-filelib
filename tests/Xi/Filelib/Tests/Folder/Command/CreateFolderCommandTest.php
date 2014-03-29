<?php

namespace Xi\Filelib\Tests\Folder\Command;

use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\Command\CreateFolderCommand;
use Xi\Filelib\Events;

class CreateFolderCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\Command\CreateFolderCommand'));
        $this->assertContains('Xi\Filelib\Command\Command', class_implements('Xi\Filelib\Folder\Command\CreateFolderCommand'));
    }

    /**
     * @test
     */
    public function commandShouldCreateFolder()
    {
        $ed = $this->getMockedEventDispatcher();

        $ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::FOLDER_BEFORE_WRITE_TO, $this->isInstanceOf('Xi\Filelib\Event\FolderEvent'));

        $ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::FOLDER_BEFORE_CREATE, $this->isInstanceOf('Xi\Filelib\Event\FolderEvent'));

        $ed
            ->expects($this->at(2))
            ->method('dispatch')
            ->with(Events::FOLDER_AFTER_CREATE, $this->isInstanceOf('Xi\Filelib\Event\FolderEvent'));

        $op = $this->getMockedFolderRepository();

        $op->expects($this->once())->method('find')->with('lusser')->will($this->returnValue($this->getMockedFolder()));

        $folder = $this
            ->getMockBuilder('Xi\Filelib\Folder\Folder')
            ->disableOriginalConstructor()
            ->setMethods(array('setUrl', 'getParentId'))
            ->getMock();
        $folder->expects($this->once())->method('setUrl')->with($this->equalTo('route'));
        $folder->expects($this->any())->method('getParentId')->will($this->returnValue('lusser'));

        $op
            ->expects($this->once())
            ->method('buildRoute')
            ->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
            ->will($this->returnValue('route'));

        $backend = $this->getMockedBackend();
        $backend
            ->expects($this->once())
            ->method('createFolder')
            ->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
            ->will($this->returnArgument(0));

        $filelib = $this->getMockedFilelib(null, null, $op, null, $ed, $backend);

        $command = new CreateFolderCommand($folder);
        $command->attachTo($filelib);

        $folder2 = $command->execute();
        $this->assertUuid($folder2->getUuid());

    }

    /**
     * @test
     */
    public function commandShouldThrowUpIfWrongfulRootIsTryingToBeCreated()
    {
        $this->setExpectedException('Xi\Filelib\LogicException');
        $command = new CreateFolderCommand(Folder::create(array('parent_id' => null, 'name' => 'manatee')));
        $command->execute();
    }

    /**
     * @test
     */
    public function respectsPresetUuid()
    {
        $folder = Folder::create(array('id' => 123));

        $command = new CreateFolderCommand($folder);
        $this->assertUuid($command->getUuid());

        $presetCommand = new CreateFolderCommand($folder);
        $presetCommand->setUuid('lipaiseppa-kvaakkua-artoseni');
        $this->assertSame('lipaiseppa-kvaakkua-artoseni', $presetCommand->getUuid());
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $folder = $this->getMock('Xi\Filelib\Folder\Folder');
        $uuid = Uuid::uuid4()->toString();

        $command = new CreateFolderCommand($folder);
        $command->setUuid($uuid);

        $serialized = serialize($command);
        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'folderRepository', $command2);
        $this->assertAttributeEquals($folder, 'folder', $command2);
        $this->assertAttributeEquals($uuid, 'uuid', $command2);

    }

    /**
     * @test
     */
    public function topicIsCorrect()
    {
        $command = $this->getMockBuilder('Xi\Filelib\Folder\Command\CreateFolderCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();

        $this->assertEquals('xi_filelib.command.folder.create', $command->getTopic());
    }

}
