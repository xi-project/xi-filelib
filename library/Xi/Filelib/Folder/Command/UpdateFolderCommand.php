<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder\Command;

use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Event\FolderEvent;
use Xi\Filelib\Events;
use Xi\Filelib\FileLibrary;
use Pekkis\Queue\Message;

class UpdateFolderCommand extends AbstractFolderCommand
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
        $route = $this->folderOperator->buildRoute($this->folder);
        $this->folder->setUrl($route);

        $this->folderOperator->getBackend()->updateFolder($this->folder);

        foreach ($this->folderOperator->findFiles($this->folder) as $file) {
            $command = $this->folderOperator->createCommand(
                'Xi\Filelib\File\Command\UpdateFileCommand',
                array(
                    $file
                )
            );
            $command->execute();
        }

        foreach ($this->folderOperator->findSubFolders($this->folder) as $subFolder) {
            $command = $this->folderOperator->createCommand(
                'Xi\Filelib\Folder\Command\UpdateFolderCommand',
                array(
                    $subFolder
                )
            );
            $command->execute();
        }

        $event = new FolderEvent($this->folder);
        $this->folderOperator->getEventDispatcher()->dispatch(
            Events::FOLDER_AFTER_UPDATE,
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
        return 'xi_filelib.command.folder.update';
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
