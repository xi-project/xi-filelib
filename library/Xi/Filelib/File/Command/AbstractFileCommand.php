<?php

namespace Xi\Filelib\File\Command;

use Xi\Filelib\File\FileOperator;
use Xi\Filelib\AbstractCommand;

abstract class AbstractFileCommand extends AbstractCommand implements FileCommand
{

    /**
     *
     * @var FileOperator
     */
    protected $fileOperator;

    public function __construct(FileOperator $fileOperator)
    {
        parent::__construct($fileOperator->generateUuid());
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
