<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder\Command;

use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Event\FolderEvent;
use Xi\Filelib\Events;
use Xi\Filelib\LogicException;
use Pekkis\Queue\Message;
use Xi\Filelib\Queue\UuidReceiver;

class CreateFolderCommand extends AbstractFolderCommand implements UuidReceiver
{
    /**
     * @var Folder
     */
    private $folder;

    /**
     * @var string
     */
    private $uuid = null;

    public function __construct(Folder $folder)
    {
        $this->folder = $folder;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    public function execute()
    {
        if ($this->folder->getParentId() === null && $this->folder->getName() !== 'root') {
            throw new LogicException('Only one root folder may exist');
        }

        if ($this->folder->getParentId()) {
            $parentFolder = $this->folderOperator->find($this->folder->getParentId());
            $event = new FolderEvent($parentFolder);
            $this->eventDispatcher->dispatch(Events::FOLDER_BEFORE_WRITE_TO, $event);
        }

        $route = $this->folderOperator->buildRoute($this->folder);
        $this->folder->setUrl($route);
        $this->folder->setUuid($this->getUuid());

        $event = new FolderEvent($this->folder);
        $this->eventDispatcher->dispatch(Events::FOLDER_BEFORE_CREATE, $event);

        $this->backend->createFolder($this->folder);

        $event = new FolderEvent($this->folder);
        $this->eventDispatcher->dispatch(Events::FOLDER_AFTER_CREATE,$event);

        return $this->folder;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid ?: Uuid::uuid4()->toString();
    }

    public function getTopic()
    {
        return 'xi_filelib.command.folder.create';
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
}
