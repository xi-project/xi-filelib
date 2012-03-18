<?php

namespace Xi\Filelib\Storage;

use Xi\Filelib\Configurator;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\FileLibrary;
    
        

/**
 * Abstract storage convenience base class with common methods implemented
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
abstract class AbstractStorage implements Storage
{
    /**
     * @var FileLibrary Filelib
     */
    private $filelib;
    
    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }
    
    
    /**
     * Sets filelib
     *
     * @param FileLibrary $filelib
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
    
    
    
    
}