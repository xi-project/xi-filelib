<?php

namespace Xi\Filelib\Linker;

/**
 * An abstract linker class with common methods implemented.
 *
 * @package Xi_Filelib
 * @author pekkis
 *
 */
abstract class AbstractLinker implements \Xi\Filelib\Linker\Linker
{

    /**
     * @var \Xi\Filelib\FileLibrary Filelib
     */
    protected $_filelib;

    /**
     * @param array|\Zend_Config $options
     */
    public function __construct($options = array())
    {
        \Xi\Filelib\Options::setConstructorOptions($this, $options);
    }


    /**
     * Sets filelib
     *
     */
    public function setFilelib(\Xi\Filelib\FileLibrary $filelib)
    {
        $this->_filelib = $filelib;
    }


    /**
     * Returns filelib
     *
     * @return \Xi\Filelib\FileLibrary
     */
    public function getFilelib()
    {
        return $this->_filelib;
    }

    /**
     * Initialization is run once when linker is set to filelib
     */
    public function init()
    { }
    


}
