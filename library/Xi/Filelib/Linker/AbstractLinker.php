<?php

namespace Xi\Filelib\Linker;

use \Xi\Filelib\Linker\Linker,
    \Xi\Filelib\FileLibrary
    ;

/**
 * An abstract linker class with common methods implemented.
 *
 * @author pekkis
 *
 */
abstract class AbstractLinker implements Linker
{
    /**
     * @var FileLibrary Filelib
     */
    protected $filelib;

    /**
     * Sets filelib
     *
     */
    public function setFilelib(FileLibrary $filelib)
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

    /**
     * Initialization is run once when linker is set to filelib
     */
    public function init()
    { }
    


}
