<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib\FileLibrary, Xi\Filelib\Configurator;

/**
 * Abstract backend implementing common methods
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
abstract class AbstractBackend implements Backend
{
    /**
     * @var Xi\Filelib\FileLibrary Filelib
     */
    private $_filelib;

    public function __construct($options = array())
    {
        \Xi\Filelib\Configurator::setConstructorOptions($this, $options);
    }
    
    
            
    /**
     * Sets filelib
     *
     * @param Xi_Filelib $filelib
     */
    public function setFilelib(FileLibrary $filelib)
    {
        $this->_filelib = $filelib;
    }

    /**
     * Returns filelib
     *
     * @return Xi\Filelib\FileLibrary
     */
    public function getFilelib()
    {
        return $this->_filelib;
    }
    
    public function init()
    { }
    
}