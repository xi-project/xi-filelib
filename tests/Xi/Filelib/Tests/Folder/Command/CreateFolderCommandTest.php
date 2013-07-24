<?php

namespace Xi\Filelib\Tests\Folder\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderOperator;
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
        $this->assertContains('Xi\Filelib\Folder\Command\FolderCommand', class_implements('Xi\Filelib\Folder\Command\CreateFolderCommand'));
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $op = $this->getMockedFolderOperator();
        $op->expects($this->any())->method('generateUuid')->will($this->returnValue('xooxer'));

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

        $command = new CreateFolderCommand($op, $folder);

        $serialized = serialize($command);
        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'folderOperator', $command2);
        $this->assertAttributeEquals($folder, 'folder', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);

    }

    /**
     * @test
     */
    public function commandShouldCreateFolder()
    {
        $filelib = $this->getMockedFilelib();


        $ed = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

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

        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($ed));

        $op = $this->getMockBuilder('Xi\Filelib\Folder\FolderOperator')
                   ->setMethods(array('buildRoute', 'generateUuid', 'find'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

        $op->expects($this->once())->method('generateUuid')
           ->will($this->returnValue('uusi-uuid'));

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

        $backend = $this
            ->getMockBuilder('Xi\Filelib\Backend\Backend')
            ->disableOriginalConstructor()
            ->getMock();

        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        $backend
            ->expects($this->once())
            ->method('createFolder')
            ->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
            ->will($this->returnArgument(0));

        $command = new CreateFolderCommand($op, $folder);

        $folder2 = $command->execute();
        $this->assertEquals('uusi-uuid', $folder2->getUuid());

    }

}
