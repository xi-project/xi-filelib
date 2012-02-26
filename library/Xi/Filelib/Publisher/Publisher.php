<?php

namespace Xi\Filelib\Publisher;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;


/**
 * Publisher interface
 * 
 * @author pekkis
 *
 */
interface Publisher
{
    
    public function __construct($options = array());
        
    /**
     * Publishes a file
     * 
     * @param File $file
     */
    public function publish(File $file);
        
    /**
     * Publishes a version of a file
     * 
     * @param File $file
     * @param VersionProvider $version
     */
    public function publishVersion(File $file, VersionProvider $version);
    
    /**
     * Unpublishes a file
     * 
     * @param File $file
     */
    public function unpublish(File $file);
    
    /**
     * Unpublishes a version of a file
     * 
     * @param File $file
     * @param VersionProvider $version
     */
    public function unpublishVersion(File $file, VersionProvider $version);
        
    /**
     * Returns url to a file
     * 
     * @param File $file
     * @return string
     */
    public function getUrl(File $file);
    
    /**
     * Returns url to a version of a file
     * 
     * @param File $file
     * @param VersionProvider $version
     * @return string
     */
    public function getUrlVersion(File $file, VersionProvider $version);
    
    
    /**
     * Sets filelib
     * @param FileLibrary
     * @return Publisher
     */
    public function setFilelib(FileLibrary $filelib);

    
}