<?php

namespace Xi\Filelib\Linker;

/**
 * Linker interface
 * 
 * @author pekkis
 * @package \Xi_Filelib
 *
 */
interface Linker
{

    /**
     * Constructor eats filelib as parameter.
     *
     * @param \Zend_Config|array $options
     */
    public function __construct($options = array());

    
    public function setFilelib(\Xi\Filelib\FileLibrary $filelib);
    
    /**
     * Returns filelib
     *
     * @return \Xi\Filelib\FileLibrary
     */
    public function getFilelib();

    /**
     * Returns link for a version of a file
     *
     * @param \Xi\Filelib\File\File $file
     * @param \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version Version plugin
     * @return string Versioned link
     */
    public function getLinkVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version);

    /**
     * Returns a link for a file
     *
     * @param \Xi\Filelib\File\File $file
     * @return string Link
     */
    public function getLink(\Xi\Filelib\File\File $file);

    
    /**
     * Initialization is run when a linker is set to filelib.
     */
    public function init();
    

}