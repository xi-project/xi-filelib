<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Event\FolderEvent;
use Xi\Filelib\Events;
use Pekkis\Queue\Message;

class DeleteFolderCommand extends AbstractFolderCommand
{
    /**
     *
     * @var FileOperator
     */
    private $fileOperator;

    /**
     *
     * @var Folder
     */
    private $folder;

    public function __construct(Folder $folder)
    {
        $this->folder = $folder;
    }

    public function execute()
    {
        $event = new FolderEvent($this->folder);
        $this->folderOperator->getEventDispatcher()->dispatch(Events::FOLDER_BEFORE_DELETE, $event);

        foreach ($this->folderOperator->findSubFolders($this->folder) as $childFolder) {
            $command = $this->folderOperator->createCommand(
                'Xi\Filelib\Folder\Command\DeleteFolderCommand',
                array(
                    $childFolder
                )
            );
            $command->execute();
        }

        foreach ($this->folderOperator->findFiles($this->folder) as $file) {
            $command = $this->folderOperator->createCommand(
                'Xi\Filelib\File\Command\DeleteFileCommand',
                array(
                    $file
                )
            );
            $command->execute();
        }

        $this->folderOperator->getBackend()->deleteFolder($this->folder);

        $event = new FolderEvent($this->folder);
        $this->folderOperator->getEventDispatcher()->dispatch(
            Events::FOLDER_AFTER_DELETE,
            $event
        );
    }

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->fileOperator = $filelib->getFileOperator();
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return Message::create(
            'xi_filelib.command.folder.delete',
            array(
                'folder_id' => $this->folder->getId(),
            )
        );
    }
}
