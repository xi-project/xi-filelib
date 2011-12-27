<?php

namespace Xi\Filelib\Storage;

use \Xi\Filelib\FileLibrary,
    \Xi\Filelib\File\File,
    \Xi\Filelib\FilelibException,
    \Xi\Filelib\Plugin\VersionProvider\VersionProvider
    ;

/**
 * Filelib Storage interface
 * 
 * @author pekkis
 * @todo Something is not perfect yet... Rethink and finalize
 *
 */
interface Storage
{
    public function __construct($options = array());
    
    /**
     * Sets filelib
     *
     * @return FileLibrary
     */
    public function setFilelib(FileLibrary $filelib);

    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib();
    
    /**
     * Stores an uploaded file
     * 
     * @param File $file
     * @param string $tempFile
     * @throws FilelibException
     */
    public function store(File $file, $tempFile);
    
    /**
     * Stores a version of a file
     * 
     * @param File $file
     * @param VersionProvider $version
     * @param string $tempFile File to be stored
     * @throws FilelibException
     */
    public function storeVersion(File $file, VersionProvider $version, $tempFile);
    
    /**
     * Retrieves a file and temporarily stores it somewhere so it can be read.
     * 
     * @param File $file
     * @return FileObject
     */
    public function retrieve(File $file);
    
    /**
     * Retrieves a version of a file and temporarily stores it somewhere so it can be read.
     * 
     * @param File $file
     * @param VersionProvider $version
     * @return FileObject
     */
    public function retrieveVersion(File $file, VersionProvider $version);
    
    /**
     * Deletes a file
     * 
     * @param File $file
     */
    public function delete(File $file);
    
    /**
     * Deletes a version of a file
     * 
     * @param File $file
     * @param VersionProvider $version
     */
    public function deleteVersion(File $file, VersionProvider $version);
    
}