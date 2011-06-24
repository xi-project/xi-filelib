<?php

namespace Xi\Filelib\Storage;

/**
 * Abstract storage convenience base class with common methods implemented
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
abstract class AbstractStorage implements \Xi\Filelib\Storage\Storage
{
    /**
     * @var \Xi\Filelib\FileLibrary Filelib
     */
    private $_filelib;
    
    public function __construct($options = array())
    {
        \Xi\Filelib\Options::setConstructorOptions($this, $options);
    }
    
    /**
     * Sets filelib
     *
     * @param \Xi_Filelib $filelib
     */
    public function setFilelib(\Xi\Filelib\FileLibrary $filelib)
    {
        $this->_filelib = $filelib;
    }

    /**
     * Returns filelib
     *
     * @return \Xi\Filelib\FileLibrary Filelib
     */
    public function getFilelib()
    {
        return $this->_filelib;
    }
    
    
    
    
}