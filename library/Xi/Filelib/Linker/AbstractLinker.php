<?php

namespace Xi\Filelib\Linker;

use Xi\Filelib\Linker\Linker,
    Xi\Filelib\FileLibrary,
    Xi\Filelib\Configurator
    ;

/**
 * An abstract linker class with common methods implemented.
 *
 * @author pekkis
 *
 */
abstract class AbstractLinker implements Linker
{
    
    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }
    
    /**
     * @var FileLibrary Filelib
     */
    protected $filelib;

    /**
     * Sets filelib
     * 
     * @return Linker
     *
     */
    public function setFilelib(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
        return $this;
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
    {
        return $this;
    }
    


}
