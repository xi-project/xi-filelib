<?php

namespace Xi\Filelib\Plugin;

/**
 * Xi Filelib plugin interface
 *
 * @package Xi_Filelib
 * @author pekkis
 *
 */
interface Plugin
{

    public function __construct($options = array());
        
    /**
     * Sets filelib
     *
     * @param \Xi_Filelib $filelib Filelib
     */
    public function setFilelib(\Xi\Filelib\FileLibrary $filelib);

    /**
     * Returns filelib
     *
     * @return \Xi\Filelib\FileLibrary
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
     */
    public function setProfiles(array $profiles);

    /**
     * Runs when plugin is added.
     */
    public function init();
    
    /**
     * Runs before upload
     *
     * @param \Xi\Filelib\File\FileUpload $upload
     * @return \Xi\Filelib\File\FileUpload
     */
    public function beforeUpload(\Xi\Filelib\File\FileUpload $upload);

    /**
     * Runs after succesful upload.
     *
     * @param \Xi\Filelib\File\File $file
     */
    public function afterUpload(\Xi\Filelib\File\File $file);

    /**
     * Runs after successful delete.
     *
     * @param \Xi\Filelib\File\File $file
     */
    public function onDelete(\Xi\Filelib\File\File $file);

    /**
     * Runs on publish
     *
     * @param \Xi\Filelib\File\File $file
     */
    public function onPublish(\Xi\Filelib\File\File $file);

    /**
     * Runs on unpublish
     *
     * @param \Xi\Filelib\File\File $file
     */
    public function onUnpublish(\Xi\Filelib\File\File $file);

}