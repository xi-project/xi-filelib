<?php

namespace Xi\Filelib\Plugin;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\File\File;
use Xi\Filelib\Event\FileProfileEvent;

/**
 * Abstract plugin class provides empty convenience methods for all hooks.
 *
 * @author pekkis
 *
 */
abstract class AbstractPlugin implements Plugin
{
    
    /**
     * @var FileLibrary Filelib
     */
    protected $filelib;

    /**
     * @var array Array of profiles
     */
    protected $profiles = array();
    
    /**
     * @var array Subscribed events
     */
    static protected $subscribedEvents = array(
        'fileprofile.add' => 'onFileProfileAdd',
    );
    
    /**
     * Returns an array of subscribed events
     * 
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return static::$subscribedEvents;
    }
    
    
    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }
    
    
    /**
     * Sets filelib
     *
     * @param FileLibrary $filelib
     * @returns Plugin
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
     * Sets the profiles attached to the plugin
     * 
     * @return Plugin
     */
    public function setProfiles(array $profiles)
    {
        $this->profiles = $profiles;
        return $this;
    }

    /** 
     * Returns an array of profiles attached to the plugin
     * 
     * @return array
     */
    public function getProfiles()
    {
        return $this->profiles;
    }

    /**
     * Returns whether plugin belongs to a profile
     * 
     * @param string $profile
     * @return boolean
     */
    public function hasProfile($profile)
    {
        return in_array($profile, $this->getProfiles());
    }
    
    
    public function init()
    { }
    
    public function onFileProfileAdd(FileProfileEvent $event)
    {
        $profile = $event->getProfile();
        if (in_array($profile->getIdentifier(), $this->getProfiles())) {
            $profile->addPlugin($this);
        }
    }
    

}
