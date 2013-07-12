<?php

namespace Xi\Filelib\Tests\Folder\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\Command\CreateFolderCommand;

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
            ->expects($this->once())
            ->method('dispatch')
            ->with(
            $this->equalTo('xi_filelib.folder.create'),
            $this->isInstanceOf('Xi\Filelib\Event\FolderEvent')
        );
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($ed));

        $op = $this->getMockBuilder('Xi\Filelib\Folder\FolderOperator')
                   ->setMethods(array('buildRoute', 'generateUuid'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

        $op->expects($this->once())->method('generateUuid')
           ->will($this->returnValue('uusi-uuid'));

        $folder = $this->getMockBuilder('Xi\Filelib\Folder\Folder')
                       ->disableOriginalConstructor()
                       ->setMethods(array('setUrl'))
                       ->getMock();

        $folder->expects($this->once())->method('setUrl')->with($this->equalTo('route'));

        $op->expects($this->once())->method('buildRoute')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))->will($this->returnValue('route'));

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
