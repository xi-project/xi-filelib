<?php

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\File\File;

class FileCopyEvent extends Event
{
    /**
     * @var File
     */
    private $source;

    /**
     * @var File
     */
    private $target;


    public function __construct(File $source, File $target)
    {
        $this->source = $source;
        $this->target = $target;
    }

    /**
     * Returns source
     *
     * @return File
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Returns target
     *
     * @return File
     */
    public function getTarget()
    {
        return $this->target;
    }



}
