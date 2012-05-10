<?php

namespace Xi\Filelib\Folder\Command;

use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\Command\AbstractFolderCommand;
use Serializable;

class CreateByUrlFolderCommand extends AbstractFolderCommand implements Serializable
{
 
    /**
     *
     * @var string
     */
    private $url;
    
    
    public function __construct(FolderOperator $folderOperator, $url)
    {
        parent::__construct($folderOperator);
        $this->url = $url;
    }
    
    
    public function execute()
    {
        $folder = $this->folderOperator->findByUrl($this->url);
        if ($folder) {
            return $folder;
        }
        
        $rootFolder = $this->folderOperator->findRoot();

        $exploded = explode('/', $this->url);

        $folderNames = array();

        $created = null;
        $previous = null;

        $count = 0;

        while (sizeof($exploded) || !$created) {

            $folderNames[] = $folderCurrent = array_shift($exploded);
            $folderName = implode('/', $folderNames);
            $created = $this->folderOperator->findByUrl($folderName);

            if (!$created) {
                $created = $this->folderOperator->getInstance(array(
                    'parent_id' => $previous ? $previous->getId() : $rootFolder->getId(),
                    'name' => $folderCurrent,
                        ));
                
                $command = $this->folderOperator->createCommand('Xi\Filelib\Folder\Command\CreateFolderCommand', array($this->folderOperator, $created));
                $command->execute();
                                
            }
            $previous = $created;
        }

        return $created;
    }
        
    
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->url = $data['url'];
    }
    
    
    public function serialize()
    {
        return serialize(array(
           'url' => $this->url,
        ));
    }
    
}