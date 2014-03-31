<?php

namespace Xi\Filelib\Tests\Folder\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Command\UpdateFolderCommand;
use ArrayIterator;
use Xi\Filelib\Events;

class UpdateFolderCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\Command\UpdateFolderCommand'));
        $this->assertContains('Xi\Filelib\Command\Command', class_implements('Xi\Filelib\Folder\Command\UpdateFolderCommand'));
    }

    /**
     * @test
     */
    public function updateShouldUpdateFoldersAndFilesRecursively()
    {

        $ed = $this->getMockedEventDispatcher();
        $ed
            ->expects($this->once())
            ->method('dispatch')
            ->with(
            $this->equalTo(Events::FOLDER_AFTER_UPDATE),
            $this->isInstanceOf('Xi\Filelib\Event\FolderEvent')
        );

        $op = $this->getMockedFolderRepository();

        $updateCommand = $this->getMockBuilder('Xi\Filelib\Folder\Command\UpdateFolderCommand')
            ->disableOriginalConstructor()
            ->getMock();

        $updateFileCommand = $this->getMockBuilder('Xi\Filelib\File\Command\UpdateFileCommand')
            ->disableOriginalConstructor()
            ->getMock();

        $updateCommand->expects($this->exactly(3))->method('execute');
        $updateFileCommand->expects($this->exactly(4))->method('execute');

        $op->expects($this->exactly(1))->method('findSubFolders')->with($this->isInstanceOf('Xi\Filelib\Folder\Folder'))
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
                function ($className) use ($updateCommand, $updateFileCommand) {

                    if ($className === 'Xi\Filelib\Folder\Command\UpdateFolderCommand') {
                        return $updateCommand;
                    } elseif ($className == 'Xi\Filelib\File\Command\UpdateFileCommand') {
                        return $updateFileCommand;
                    }
                }
            )
        );

        $fiop = $this->getMockedFileRepository();

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Backend')->disableOriginalConstructor()->getMock();

        $folder = Folder::create(array('id' => 1));

        $backend
            ->expects($this->once())
            ->method('updateFolder')
            ->with($folder);

        $filelib = $this->getMockedFilelib(null, $fiop, $op, null, $ed, $backend);

        $command = new UpdateFolderCommand($folder);
        $command->attachTo($filelib);

        $command->execute();
    }

    /**
     * @test
     */
    public function topicIsCorrect()
    {
        $command = $this->getMockBuilder('Xi\Filelib\Folder\Command\UpdateFolderCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();

        $this->assertEquals('xi_filelib.command.folder.update', $command->getTopic());
    }
}
