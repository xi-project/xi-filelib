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
        parent::__construct();
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

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->folder = $data['folder'];
        $this->uuid = $data['uuid'];
    }

    public function serialize()
    {
        return serialize(
            array(
                'folder' => $this->folder,
                'uuid' => $this->uuid,
            )
        );
    }

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->fileOperator = $filelib->getFileOperator();
    }
}
