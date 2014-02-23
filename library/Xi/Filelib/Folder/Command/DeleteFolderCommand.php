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
        $this->eventDispatcher->dispatch(Events::FOLDER_BEFORE_DELETE, $event);

        foreach ($this->folderOperator->findSubFolders($this->folder) as $childFolder) {
            $this->folderOperator->createCommand(
                FolderOperator::COMMAND_DELETE,
                array(
                    $childFolder
                )
            )->execute();
        }

        foreach ($this->folderOperator->findFiles($this->folder) as $file) {
            $this->folderOperator->createCommand(
                FileOperator::COMMAND_DELETE,
                array(
                    $file
                )
            )->execute();
        }

        $this->backend->deleteFolder($this->folder);

        $event = new FolderEvent($this->folder);
        $this->eventDispatcher->dispatch(
            Events::FOLDER_AFTER_DELETE,
            $event
        );
    }

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->fileOperator = $filelib->getFileOperator();
    }

    public function getTopic()
    {
        return 'xi_filelib.command.folder.delete';
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->folder = $data['folder'];
    }

    public function serialize()
    {
        return serialize(
            array(
                'folder' => $this->folder,
            )
        );
    }
}
