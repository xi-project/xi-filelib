<?php

namespace Xi\Filelib\Folder\Command;

use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Serializable;

class CreateFolderCommand extends AbstractFolderCommand implements Serializable
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
        return $folder;
    }


    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->folder = $data['folder'];
    }


    public function serialize()
    {
        return serialize(array(
           'folder' => $this->folder,
        ));
    }

}