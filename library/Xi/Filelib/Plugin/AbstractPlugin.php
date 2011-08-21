<?php

namespace Xi\Filelib\Plugin;

/**
 * Abstract plugin class provides convenience methods for all event hooks.
 *
 * @package Xi_Filelib
 * @author pekkis
 *
 */
abstract class AbstractPlugin implements \Xi\Filelib\Plugin\Plugin
{
        
    
    /**
     * @var \Xi\Filelib\FileLibrary Filelib
     */
    protected $_filelib;

    /**
     * @var array Array of profiles
     */
    protected $_profiles;
    
    public function __construct($options = array())
    {
        \Xi\Filelib\Configurator::setConstructorOptions($this, $options);
    }
    
    /** 
     * Returns an array of profiles attached to the plugin
     * 
     * @return array
     */
    public function getProfiles()
    {
        return $this->_profiles;
    }

    /** 
     * Sets the profiles attached to the plugin
     * 
     * @return array
     */
    public function setProfiles(array $profiles)
    {
        $this->_profiles = $profiles;
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
     * @return \Xi\Filelib\FileLibrary
     */
    public function getFilelib()
    {
        return $this->_filelib;
    }

    public function init()
    { }

    public function beforeUpload(\Xi\Filelib\File\FileUpload $upload)
    {
        return $upload;
    }

    public function afterUpload(\Xi\Filelib\File\File $file)
    { }

    public function onDelete(\Xi\Filelib\File\File $file)
    { }
    
    public function onPublish(\Xi\Filelib\File\File $file)
    { }
    
    public function onUnpublish(\Xi\Filelib\File\File $file)
    { }
    

}
