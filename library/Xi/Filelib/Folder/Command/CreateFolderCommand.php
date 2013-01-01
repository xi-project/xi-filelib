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

class CreateFolderCommand extends AbstractFolderCommand
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


    public function __construct(FolderOperator $folderOperator, Folder $folder)
    {
        parent::__construct($folderOperator);
        $this->folder = $folder;
    }


    public function execute()
    {
        $route = $this->folderOperator->buildRoute($this->folder);
        $this->folder->setUrl($route);
        $this->folder->setUuid($this->getUuid());
        $folder = $this->folderOperator->getBackend()->createFolder($this->folder);

        $event = new FolderEvent($folder);
        $this->folderOperator->getEventDispatcher()->dispatch(
            'folder.create',
            $event
        );
        return $folder;
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
