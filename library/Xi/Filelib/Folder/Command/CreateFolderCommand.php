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

class CreateFolderCommand extends AbstractFolderCommand
{
    /**
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
        if ($this->folder->getParentId()) {
            $parentFolder = $this->folderOperator->find($this->folder->getParentId());
            $event = new FolderEvent($parentFolder);
            $this->folderOperator->getEventDispatcher()->dispatch(Events::FOLDER_BEFORE_WRITE_TO, $event);
        }

        $route = $this->folderOperator->buildRoute($this->folder);
        $this->folder->setUrl($route);
        $this->folder->setUuid($this->getUuid());

        $event = new FolderEvent($this->folder);
        $this->folderOperator->getEventDispatcher()->dispatch(Events::FOLDER_BEFORE_CREATE, $event);

        $this->folderOperator->getBackend()->createFolder($this->folder);

        $event = new FolderEvent($this->folder);
        $this->folderOperator->getEventDispatcher()->dispatch(
            Events::FOLDER_AFTER_CREATE,
            $event
        );

        return $this->folder;
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->folder = $data['folder'];
        $this->uuid = $data['uuid'];
    }

    public function serialize()
    {
        return serialize(array(
           'folder' => $this->folder,
           'uuid' => $this->uuid,
        ));
    }

}
