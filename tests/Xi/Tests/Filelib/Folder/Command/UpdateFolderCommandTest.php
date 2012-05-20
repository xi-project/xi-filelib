<?php

namespace Xi\Tests\Filelib\Folder\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\DefaultFolderOperator;
use Xi\Filelib\File\DefaultFileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Upload\FileUpload;

use Xi\Filelib\Folder\Command\UpdateFolderCommand;

class UpdateFolderCommandTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\Command\UpdateFolderCommand'));
        $this->assertContains('Xi\Filelib\Folder\Command\FolderCommand', class_implements('Xi\Filelib\Folder\Command\UpdateFolderCommand'));
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

        $fop = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array())
                    ->getMock();

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

        $command = new UpdateFolderCommand($op, $fop, $folder);

        $serialized = serialize($command);
        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileOperator', $command2);
        $this->assertAttributeEquals(null, 'folderOperator', $command2);
        $this->assertAttributeEquals($folder, 'folder', $command2);

    }

        /**
     * @test
     */
    public function deleteShouldUpdateFoldersAndFilesRecursively()
    {
        $filelib = new FileLibrary();

         $op = $this->getMockBuilder('Xi\Filelib\Folder\DefaultFolderOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array('createCommand'))
                    ->getMock();

         $deleteCommand = $this->getMockBuilder('Xi\Filelib\Folder\Command\UpdateFolderCommand')
                               ->disableOriginalConstructor()
                               ->getMock();

         $deleteFileCommand = $this->getMockBuilder('Xi\Filelib\File\Command\DeleteFileCommand')
                               ->disableOriginalConstructor()
                               ->getMock();


        $deleteCommand->expects($this->exactly(3))->method('execute');
        $deleteFileCommand->expects($this->exactly(4))->method('execute');


        $op->expects($this->at(0))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UpdateFileCommand'))
                                       ->will($this->returnValue($deleteFileCommand));
        $op->expects($this->at(1))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UpdateFileCommand'))
                                       ->will($this->returnValue($deleteFileCommand));

        $op->expects($this->at(2))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UpdateFileCommand'))
                                       ->will($this->returnValue($deleteFileCommand));
        $op->expects($this->at(3))->method('createCommand')->with($this->equalTo('Xi\Filelib\File\Command\UpdateFileCommand'))
                                       ->will($this->returnValue($deleteFileCommand));



        $op->expects($this->at(4))->method('createCommand')->with($this->equalTo('Xi\Filelib\Folder\Command\UpdateFolderCommand'))
                                       ->will($this->returnValue($deleteCommand));
        $op->expects($this->at(5))->method('createCommand')->with($this->equalTo('Xi\Filelib\Folder\Command\UpdateFolderCommand'))
                                       ->will($this->returnValue($deleteCommand));
        $op->expects($this->at(6))->method('createCommand')->with($this->equalTo('Xi\Filelib\Folder\Command\UpdateFolderCommand'))
                                       ->will($this->returnValue($deleteCommand));


        $backend = $this->getMockForAbstractClass('Xi\Filelib\Backend\Backend');
        $backend->expects($this->exactly(1))->method('findSubFolders')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
                ->will($this->returnCallback(function($folder) {

                    if($folder->getId() == 1) {
                        return array(
                            array('id' => 2, 'parent_id' => 1),
                            array('id' => 3, 'parent_id' => 1),
                            array('id' => 4, 'parent_id' => 1),
                        );
                    }
                    return array();
                 }));
        $backend->expects($this->exactly(1))->method('findFilesIn')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
                ->will($this->returnCallback(function($folder) {

                    if($folder->getId() == 1) {
                        return array(
                            array('id' => 1, 'name' => 'tohtori-vesala.avi'),
                            array('id' => 2, 'name' => 'tohtori-vesala.png'),
                            array('id' => 3, 'name' => 'tohtori-vesala.jpg'),
                            array('id' => 4, 'name' => 'tohtori-vesala.bmp'),
                        );
                    }
                    return array();
                 }));

        $backend->expects($this->exactly(1))->method('UpdateFolder')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'));

        $fiop = $this->getMockBuilder('Xi\Filelib\File\DefaultFileOperator')
                      ->setMethods(array('delete'))
                      ->setConstructorArgs(array($filelib))
                      ->getMock();


        $filelib->setBackend($backend);
        $filelib->setFileOperator($fiop);

        $folder = Folder::create(array('id' => 1));

        $command = new UpdateFolderCommand($op, $fiop, $folder);
        $command->execute();


    }




}

