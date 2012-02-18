<?php

namespace Xi\Filelib\File;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Plugin\Plugin;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\File\FileProfile;
use Xi\Filelib\FilelibException;
use Xi\Filelib\File\Upload\FileUpload;

/**
 *
 * @author pekkis
 */
interface FileOperator
{    
    
    /**
     * Returns an instance of the currently set fileitem class
     * 
     * @param mixed $data Data as array or a file instance
     * @return File
     */
    public function getInstance($data = null);
    
    /**
     * Adds a file profile
     * 
     * @param FileProfile $profile
     * @return FileLibrary
     */
    public function addProfile(FileProfile $profile);

    /**
     * Returns a file profile
     * 
     * @param string $identifier File profile identifier
     * @throws FilelibException
     * @return FileProfile
     */
    public function getProfile($identifier);

    /**
     * Returns all file profiles
     * 
     * @return array Array of file profiles
     */
    public function getProfiles();

    /**
     * Updates a file
     *
     * @param File $file
     * @return unknown_type
     */
    public function update(File $file);

    /**
     * Finds a file
     *
     * @param mixed $id File id
     * @return File
     */
    public function find($id);
    
    public function findByFilename(Folder $folder, $filename);
    

    /**
     * Finds and returns all files
     *
     * @return \ArrayIterator
     */
    public function findAll();

    /**
     * Returns whether a file is anonymous
     *
     * @todo This is still mock!
     * @param \Xi\Filelib\File\File $file File
     * @return boolean
     */
    public function isReadableByAnonymous(File $file);

    /**
     * Gets a new upload
     *
     * @param string $path Path to upload file
     * @return FileUpload
     */
    public function prepareUpload($path);
    

    /**
     * Uploads file to filelib.
     *
     * @param mixed $upload Uploadable, path or object
     * @param Folder $folder
     * @return File
     * @throws FilelibException
     */
    public function upload($upload, Folder $folder, $profile = 'default');

    /**
     * Deletes a file
     *
     * @param File $file
     * @throws FilelibException
     */
    public function delete(File $file);

    /**
     * Returns file type of a file
     *
     * @param File File $file item
     * @return string File type
     */
    public function getType(File $file);

    /**
     * Returns whether a file has a certain version
     *
     * @param \Xi\Filelib\File\File $file File item
     * @param string $version Version
     * @return boolean
     */
    public function hasVersion(File $file, $version);


    /**
     * Returns version provider for a file/version
     *
     * @param File $file File item
     * @param string $version Version
     * @return object Provider
     */
    public function getVersionProvider(File $file, $version);
    
    public function publish(File $file);
    
    public function unpublish(File $file);
    
    /**
     * Sets file item class name
     */
    public function setClass($className);
    
    /**
     * Sets file item class name
     */
    public function getClass();

    
    public function addPlugin(Plugin $plugin, $priority = 0);
    
    /**
     * @return Publisher
     */
    public function getPublisher();
    
    /**
     * @return Storage
     */
    public function getStorage();
    
    /**
     * @return Backend
     */
    public function getBackend();
    
    /**
     * @return Acl
     */
    public function getAcl();
    
    
}
