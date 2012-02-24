<?php

namespace Xi\Filelib\Plugin;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\File\File;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Xi Filelib plugin interface
 *
 * @package Xi_Filelib
 * @author pekkis
 *
 */
interface Plugin extends EventSubscriberInterface
{

    public function __construct($options = array());

    /**
     * Sets filelib
     *
     * @param FileLibrary $filelib Filelib
     * @return Plugin
     */
    public function setFilelib(FileLibrary $filelib);

    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib();

    /**
     * Returns an array of profiles
     * 
     * @return array
     */
    public function getProfiles();

    /**
     * Sets profiles
     * 
     * @param array $profiles Array of profiles
     * @return Plugin
     */
    public function setProfiles(array $profiles);
    
    /**
     * Returns whether plugin has a certain profile
     * 
     * @return boolean
     */
    public function hasProfile($profile);
    

    /**
     * Runs when plugin is added.
     */
    public function init();


}