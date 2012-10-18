<?php

namespace Xi\Filelib\Folder\Command;

use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\AbstractCommand;

abstract class AbstractFolderCommand extends AbstractCommand implements FolderCommand
{

    /**
     *
     * @var FolderOperator
     */
    protected $folderOperator;

    public function __construct(FolderOperator $folderOperator)
    {
        parent::__construct($folderOperator->generateUuid());
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
