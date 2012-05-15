<?php

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\FileLibrary;

class FilelibEvent extends Event
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
    }

    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->filelib;
    }

}
