<?php

namespace Xi\Filelib\Publisher;

/**
 * Publisher interface
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
interface Publisher
{
    
    public function __construct($options = array());
        
    /**
     * Publishes a file
     * 
     * @param \Xi\Filelib\File\File $file
     */
    public function publish(\Xi\Filelib\File\File $file);
        
    /**
     * Publishes a version of a file
     * 
     * @param \Xi\Filelib\File\File $file
     * @param \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version
     */
    public function publishVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version);
    
    /**
     * Unpublishes a file
     * 
     * @param \Xi\Filelib\File\File $file
     */
    public function unpublish(\Xi\Filelib\File\File $file);
    
    /**
     * Unpublishes a version of a file
     * 
     * @param \Xi\Filelib\File\File $file
     * @param \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version
     */
    public function unpublishVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version);
        
    /**
     * Returns url to a file
     * 
     * @param \Xi\Filelib\File\File $file
     * @return string
     */
    public function getUrl(\Xi\Filelib\File\File $file);
    
    /**
     * Returns url to a version of a file
     * 
     * @param \Xi\Filelib\File\File $file
     * @param \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version
     * @return string
     */
    public function getUrlVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version);
    
}