<?php

namespace Xi\Filelib\Folder\Command;

use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Serializable;

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


    public function __construct(FolderOperator $folderOperator, FileOperator $fileOperator, Folder $folder)
    {
        parent::__construct($folderOperator);
        $this->fileOperator = $fileOperator;
        $this->folder = $folder;
    }


    public function execute()
    {
        $route = $this->folderOperator->buildRoute($this->folder);
        $this->folder->setUrl($route);

        $this->folderOperator->getBackend()->updateFolder($this->folder);

        foreach ($this->folderOperator->findFiles($this->folder) as $file) {
            $command = $this->folderOperator->createCommand('Xi\Filelib\File\Command\UpdateFileCommand', array(
                $this->fileOperator,
                $file
            ));
            $command->execute();
        }

        foreach ($this->folderOperator->findSubFolders($this->folder) as $subFolder) {
            $command = $this->folderOperator->createCommand('Xi\Filelib\Folder\Command\UpdateFolderCommand', array(
                $this->folderOperator,
                $this->fileOperator,
                $subFolder
            ));
            $command->execute();
        }

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