<?php

namespace Xi\Filelib\Tests\Folder\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Command\DeleteFolderCommand;
use ArrayIterator;

class DeleteFolderCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\Command\DeleteFolderCommand'));
        $this->assertContains('Xi\Filelib\Folder\Command\FolderCommand', class_implements('Xi\Filelib\Folder\Command\DeleteFolderCommand'));
    }


    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this->getMockBuilder('Xi\Filelib\Folder\FolderOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array('createCommand'))
                    ->getMock();

        $fop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                    ->setConstructorArgs(array($filelib))
                    ->setMethods(array())
                    ->getMock();

        $folder = $this->getMock('Xi\Filelib\Folder\Folder');

        $command = new DeleteFolderCommand($op, $fop, $folder);

        $serialized = serialize($command);
        $command2 = unserialize($serialized);

        $this->assertAttributeEquals(null, 'fileOperator', $command2);
        $this->assertAttributeEquals(null, 'folderOperator', $command2);
        $this->assertAttributeEquals($folder, 'folder', $command2);
        $this->assertAttributeNotEmpty('uuid', $command2);

    }

        /**
     * @test
     */
    public function deleteShouldDeleteFoldersAndFilesRecursively()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this
            ->getMockBuilder('Xi\Filelib\Folder\FolderOperator')
            ->setConstructorArgs(array($filelib))
            ->setMethods(array('createCommand', 'findSubFolders', 'findFiles'))
            ->getMock();


        $ed = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $ed
            ->expects($this->once())
            ->method('dispatch')
            ->with(
            $this->equalTo('xi_filelib.folder.delete'),
            $this->isInstanceOf('Xi\Filelib\Event\FolderEvent')
        );
        $filelib->expects($this->any())->method('getEventDispatcher')->will($this->returnValue($ed));


        $deleteCommand = $this->getMockBuilder('Xi\Filelib\Folder\Command\DeleteFolderCommand')
                              ->disableOriginalConstructor()
                              ->getMock();

        $deleteFileCommand = $this->getMockBuilder('Xi\Filelib\File\Command\DeleteFileCommand')
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $deleteCommand->expects($this->exactly(3))->method('execute');
        $deleteFileCommand->expects($this->exactly(4))->method('execute');

        $op->expects($this->exactly(1))->method('findSubFolders')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
            ->will($this->returnCallback(function($folder) {

            if($folder->getId() == 1) {
                return new ArrayIterator(array(
                    Folder::create(array('id' => 2, 'parent_id' => 1)),
                    Folder::create(array('id' => 3, 'parent_id' => 1)),
                    Folder::create(array('id' => 4, 'parent_id' => 1)),
                ));
            }
            return new ArrayIterator(array());
        }));

        $op->expects($this->exactly(1))->method('findFiles')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
            ->will($this->returnCallback(function ($folder) {

            if($folder->getId() == 1) {
                return new ArrayIterator(array(
                    Folder::create(array('id' => 1, 'name' => 'tohtori-vesala.avi')),
                    Folder::create(array('id' => 2, 'name' => 'tohtori-vesala.png')),
                    Folder::create(array('id' => 3, 'name' => 'tohtori-vesala.jpg')),
                    Folder::create(array('id' => 4, 'name' => 'tohtori-vesala.bmp')),
                ));
            }
            return new ArrayIterator(array());
        }));

        $op
            ->expects($this->any())
            ->method('createCommand')
            ->will(
                $this->returnCallback(
                    function ($className) use ($deleteCommand, $deleteFileCommand) {

                        if ($className === 'Xi\Filelib\Folder\Command\DeleteFolderCommand') {
                            return $deleteCommand;
                        } elseif ($className == 'Xi\Filelib\File\Command\DeleteFileCommand') {
                            return $deleteFileCommand;
                        }
                    }
                )
            );

        $fiop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                      ->setMethods(array('delete'))
                      ->setConstructorArgs(array($filelib))
                      ->getMock();

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')->disableOriginalConstructor()->getMock();


        $filelib->expects($this->any())->method('getBackend')->will($this->returnValue($backend));

        $folder = Folder::create(array('id' => 1));

        $backend
            ->expects($this->once())
            ->method('deleteFolder')
            ->with($folder);

        $command = new DeleteFolderCommand($op, $fiop, $folder);
        $command->execute();
    }
}

