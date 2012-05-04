<?php

namespace Xi\Filelib\File\Command;

use Xi\Filelib\File\FileOperator;

abstract class AbstractFileCommand implements FileCommand
{
    
    /**
     *
     * @var FileOperator
     */
    protected $fileOperator;
    
    public function __construct(FileOperator $fileOperator)
    {
        $this->fileOperator = $fileOperator;
    }
    
    /**
     * Returns fileoperator
     * 
     * @return FileOperator
     */
    public function getFileOperator()
    {
        return $this->fileOperator;
    }
    
    
}
