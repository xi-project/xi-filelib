<?php

namespace Xi\Filelib\Storage;

/**
 * Storage interface
 * 
 * @author pekkis
 * @package Xi_Filelib
 * @todo Something is not perfect yet... Rethink and finalize
 *
 */
interface Storage
{
    public function __construct($options = array());
    
    /**
     * Sets filelib
     *
     * @return \Xi\Filelib\FileLibrary Filelib
     */
    public function setFilelib(\Xi\Filelib\FileLibrary $filelib);

    /**
     * Returns filelib
     *
     * @return \Xi\Filelib\FileLibrary Filelib
     */
    public function getFilelib();
    
    /**
     * Stores an uploaded file
     * 
     * @param \Xi\Filelib\File\File $file
     * @param string $tempFile
     * @throws \Xi\Filelib\FilelibException
     */
    public function store(\Xi\Filelib\File\File $file, $tempFile);
    
    /**
     * Stores a version of a file
     * 
     * @param \Xi\Filelib\File\File $file
     * @param \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version
     * @param string $tempFile File to be stored
     * @throws \Xi\Filelib\FilelibException
     */
    public function storeVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version, $tempFile);
    
    /**
     * Retrieves a file and temporarily stores it somewhere so it can be read.
     * 
     * @param \Xi\Filelib\File\File $file
     * @return \Xi\Filelib\File\FileObject
     */
    public function retrieve(\Xi\Filelib\File\File $file);
    
    /**
     * Retrieves a version of a file and temporarily stores it somewhere so it can be read.
     * 
     * @param \Xi\Filelib\File\File $file
     * @param \Xi_Filelib_VersionProvider_Interface $version
     * @return \Xi\Filelib\File\FileObject
     */
    public function retrieveVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version);
    
    /**
     * Deletes a file
     * 
     * @param \Xi\Filelib\File\File $file
     */
    public function delete(\Xi\Filelib\File\File $file);
    
    /**
     * Deletes a version of a file
     * 
     * @param \Xi\Filelib\File\File $file
     * @param \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version
     */
    public function deleteVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version);
    
}