<?php

namespace Xi\Filelib\File;

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
     * @param \Xi\Filelib\File\FileProfile $profile
     * @return \Xi\Filelib\FileLibrary
     */
    public function addProfile(FileProfile $profile);

    /**
     * Returns a file profile
     * 
     * @param string $identifier File profile identifier
     * @throws \Xi\Filelib\FilelibException
     * @return \Xi\Filelib\File\FileProfile
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
     * @param \Xi\Filelib\File\File $file
     * @return unknown_type
     */
    public function update(\Xi\Filelib\File\File $file);

    /**
     * Finds a file
     *
     * @param mixed $idFile File id
     * @return \Xi\Filelib\File\File
     */
    public function find($id);
    
    public function findByFilename(\Xi\Filelib\Folder\Folder $folder, $filename);
    

    /**
     * Finds and returns all files
     *
     * @return \Xi\Filelib\File\FileIterator
     */
    public function findAll();

    /**
     * Returns whether a file is anonymous
     *
     * @todo This is still mock!
     * @param \Xi\Filelib\File\File $file File
     * @return boolean
     */
    public function isReadableByAnonymous(\Xi\Filelib\File\File $file);

    /**
     * Gets a new upload
     *
     * @param string $path Path to upload file
     * @return \Xi\Filelib\File\Upload\FileUpload
     */
    public function prepareUpload($path);
    
    /**
     * Uploads many files at once
     * 
     * @param Iterator $batch Collection of \SplFileInfo objects
     * @return ArrayIterator Collection of uploaded file items
     */
    public function uploadBatch(\Iterator $batch, $folder, $profile = 'default');

    /**
     * Uploads file to filelib.
     *
     * @param mixed $upload Uploadable, path or object
     * @param \Xi\Filelib\Folder\Folder $folder
     * @return \Xi\Filelib\File\File
     * @throws \Xi\Filelib\FilelibException
     */
    public function upload($upload, $folder, $profile = 'default');

    /**
     * Deletes a file
     *
     * @param \Xi\Filelib\File\File $file
     * @throws \Xi\Filelib\FilelibException
     */
    public function delete(\Xi\Filelib\File\File $file);

    /**
     * Returns file type of a file
     *
     * @param \Xi\Filelib\File\File File $file item
     * @return string File type
     */
    public function getType(\Xi\Filelib\File\File $file);

    /**
     * Returns whether a file has a certain version
     *
     * @param \Xi\Filelib\File\File $file File item
     * @param string $version Version
     * @return boolean
     */
    public function hasVersion(\Xi\Filelib\File\File $file, $version);


    /**
     * Returns version provider for a file/version
     *
     * @param \Xi\Filelib\File\File $file File item
     * @param string $version Version
     * @return object Provider
     */
    public function getVersionProvider(\Xi\Filelib\File\File $file, $version);
    
    public function getUrl(\Xi\Filelib\File\File $file, $opts = array());

    /**
     * Renders a file to a response
     *
     * @param \Xi_Filelib File $file item
     * @param \Zend_Controller_Response_Http $response Response
     * @param array $opts Options
     */
    public function render(\Xi\Filelib\File\File $file, $opts = array());
    
    public function publish(\Xi\Filelib\File\File $file);
    
    public function unpublish(\Xi\Filelib\File\File $file);
    
}
