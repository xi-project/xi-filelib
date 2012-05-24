<?php

namespace Xi\Tests\Filelib\Folder\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\DefaultFolderOperator;
use Xi\Filelib\File\DefaultFileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Upload\FileUpload;

use Xi\Filelib\Folder\Command\CreateFolderCommand;

class CreateFolderCommandTest extends \Xi\Tests\Filelib\TestCase
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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this->getMockBuilder('Xi\Filelib\Folder\DefaultFolderOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array('createCommand'))
                    ->getMock();

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

        $command = new CreateFolderCommand($op, $folder);

        $serialized = serialize($command);
        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'folderOperator', $command2);
        $this->assertAttributeEquals($folder, 'folder', $command2);

    }



    /**
     * @test
     */
    public function commandShouldCreateFolder()
    {
        $filelib = new FileLibrary();
        $op = $this->getMockBuilder('Xi\Filelib\Folder\DefaultFolderOperator')
                   ->setMethods(array('buildRoute', 'generateUuid'))
                   ->setConstructorArgs(array($filelib))
                   ->getMock();

        $op->expects($this->once())->method('generateUuid')
           ->will($this->returnValue('uusi-uuid'));

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');
        $folder->expects($this->once())->method('setUrl')->with($this->equalTo('route'));

        $op->expects($this->once())->method('buildRoute')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))->will($this->returnValue('route'));

        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->once())->method('createFolder')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))->will($this->returnArgument(0));

        $filelib->setBackend($backend);

        $command = new CreateFolderCommand($op, $folder);

        $folder2 = $command->execute();

    }




}

