<?php

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\File\File;

class FileEvent extends Event
{
    /**
     * @var File
     */
    private $file;
    
    public function __construct(File $file)
    {
        $this->file = $file;
    }
    
    /**
     * Returns file
     * 
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }
    
}
