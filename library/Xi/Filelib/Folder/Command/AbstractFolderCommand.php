<?php

namespace Xi\Filelib\Folder\Command;

use Xi\Filelib\Folder\FolderOperator;

abstract class AbstractFolderCommand implements FolderCommand
{
    
    /**
     *
     * @var FolderOperator
     */
    protected $folderOperator;
    
    public function __construct(FolderOperator $folderOperator)
    {
        $this->folderOperator = $folderOperator;
    }
    
    /**
     * Returns folderoperator
     * 
     * @return FolderOperator
     */
    public function getFolderOperator()
    {
        return $this->folderOperator;
    }
    
    
}
