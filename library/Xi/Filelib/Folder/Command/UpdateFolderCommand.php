<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder\Command;

use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Event\FolderEvent;
use Xi\Filelib\Events;
use Xi\Filelib\FileLibrary;
use Pekkis\Queue\Message;

class UpdateFolderCommand extends BaseFolderCommand
{
    /**
     *
     * @var FileRepository
     */
    private $fileRepository;

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
        $route = $this->folderRepository->buildRoute($this->folder);
        $this->folder->setUrl($route);

        $this->backend->updateFolder($this->folder);

        foreach ($this->folderRepository->findFiles($this->folder) as $file) {
            $this->folderRepository->createCommand(
                FileRepository::COMMAND_UPDATE,
                array(
                    $file
                )
            )->execute();
        }

        foreach ($this->folderRepository->findSubFolders($this->folder) as $subFolder) {
            $this->folderRepository->createCommand(
                FolderRepository::COMMAND_UPDATE,
                array(
                    $subFolder
                )
            )->execute();
        }

        $event = new FolderEvent($this->folder);
        $this->eventDispatcher->dispatch(Events::FOLDER_AFTER_UPDATE, $event);
    }

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->fileRepository = $filelib->getFileRepository();
    }

    public function getTopic()
    {
        return 'xi_filelib.command.folder.update';
    }
}
