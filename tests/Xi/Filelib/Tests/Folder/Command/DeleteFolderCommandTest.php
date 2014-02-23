<?php

namespace Xi\Filelib\Tests\Folder\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Command\DeleteFolderCommand;
use ArrayIterator;
use Xi\Filelib\Events;

class DeleteFolderCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\Command\DeleteFolderCommand'));
        $this->assertContains('Xi\Filelib\Command\Command', class_implements('Xi\Filelib\Folder\Command\DeleteFolderCommand'));
    }

    /**
     * @test
     */
    public function deleteShouldDeleteFoldersAndFilesRecursively()
    {
        $filelib = $this->getMockedFilelib();

        $op = $this->getMockedFolderOperator();

        $ed = $this->getMockedEventDispatcher();

        $ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::FOLDER_BEFORE_DELETE,  $this->isInstanceOf('Xi\Filelib\Event\FolderEvent'));

        $ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(Events::FOLDER_AFTER_DELETE,  $this->isInstanceOf('Xi\Filelib\Event\FolderEvent'));

        $deleteCommand = $this->getMockBuilder('Xi\Filelib\Folder\Command\DeleteFolderCommand')
                              ->disableOriginalConstructor()
                              ->getMock();

        $deleteFileCommand = $this->getMockBuilder('Xi\Filelib\File\Command\DeleteFileCommand')
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $deleteCommand->expects($this->exactly(3))->method('execute');
        $deleteFileCommand->expects($this->exactly(4))->method('execute');

        $op
            ->expects($this->exactly(1))
            ->method('findSubFolders')
            ->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
            ->will($this->returnCallback(function($folder) {

            if ($folder->getId() == 1) {
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

            if ($folder->getId() == 1) {
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

        $fiop = $this->getMockedFileOperator();

        $backend = $this->getMockedBackend();

        $folder = Folder::create(array('id' => 1));

        $backend
            ->expects($this->once())
            ->method('deleteFolder')
            ->with($folder);

        $filelib = $this->getMockedFilelib(null, $fiop, $op, null, $ed, $backend);

        $command = new DeleteFolderCommand($folder);
        $command->attachTo($filelib);
        $command->execute();
    }

    /**
     * @test
     */
    public function commandShouldSerializeAndUnserializeProperly()
    {
        $folder = $this->getMockedFolder();

        $command = new DeleteFolderCommand($folder);

        $serialized = serialize($command);
        $command2 = unserialize($serialized);

        $this->assertAttributeEquals($folder, 'folder', $command2);
    }

    /**
     * @test
     */
    public function topicIsCorrect()
    {
        $command = $this->getMockBuilder('Xi\Filelib\Folder\Command\DeleteFolderCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();

        $this->assertEquals('xi_filelib.command.folder.delete', $command->getTopic());
    }

}
