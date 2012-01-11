<?php

namespace Xi\Filelib\Backend;

/**
 * Filelib backend interface
 *
 * @package Xi_Filelib
 * @author pekkis
 *
 */
interface Backend
{

    public function __construct($options = array());
    
    
    /**
     * Initialization. Is run when backend is set to filelib.
     */
    public function init();

    /**
     * Finds folder
     *
     * @param integer $id
     * @return Xi\Filelib\Folder\Folder|false
     */
    public function findFolder($id);

    /**
     * Finds subfolders of a folder
     *
     * @param \Xi\Filelib\Folder\Folder $id
     * @return \Xi\Filelib\Folder\FolderIterator
     */
    public function findSubFolders(\Xi\Filelib\Folder\Folder $folder);

    /**
     * Finds all files
     *
     * @return \Xi\Filelib\File\FileIterator
     */
    public function findAllFiles();

    /**
     * Finds a file
     *
     * @param integer $id
     * @return \Xi\Filelib\File\File|false
     */
    public function findFile($id);

    /**
     * Finds a file
     *
     * @param \Xi\Filelib\Folder\Folder $folder
     * @return \Xi\Filelib\File\FileIterator
     */
    public function findFilesIn(\Xi\Filelib\Folder\Folder $folder);

    /**
     * Uploads a file
     *
     * @param \Xi\Filelib\File\File $file File to upload
     * @param \Xi\Filelib\Folder\Folder $folder Folder
     * @return \Xi\Filelib\File\File File item
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function upload(\Xi\Filelib\File\File $file, \Xi\Filelib\Folder\Folder $folder);

    /**
     * Creates a folder
     *
     * @param Xi\Filelib\Folder\Folder $folder
     * @return Xi\Filelib\Folder\Folder Created folder
     * @throws Xi\Filelib\FilelibException When fails
     */
    public function createFolder(\Xi\Filelib\Folder\Folder $folder);


    /**
     * Deletes a folder
     *
     * @param \Xi\Filelib\Folder\Folder $folder
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function deleteFolder(\Xi\Filelib\Folder\Folder $folder);

    /**
     * Deletes a file
     *
     * @param \Xi\Filelib\File\File $file
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function deleteFile(\Xi\Filelib\File\File $file);

    /**
     * Updates a folder
     *
     * @param \Xi\Filelib\Folder\Folder $folder
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function updateFolder(\Xi\Filelib\Folder\Folder $folder);

    /**
     * Updates a file
     *
     * @param \Xi\Filelib\File\File $file
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function updateFile(\Xi\Filelib\File\File $file);
    	
    /**
     * Returns the root folder. Creates it if it does not exist.
     *
     * @return \Xi\Filelib\Folder\Folder
     */
    public function findRootFolder();
    
    /**
     * Finds folder by url
     *
     * @param  integer                          $id
     * @return \Xi\Filelib\Folder\Folder|false
     */
    public function findFolderByUrl($url);
        
    /**
     * Finds file in a folder by filename
     * 
     * @param unknown_type $folder
     * @param unknown_type $filename
     */
    public function findFileByFilename(\Xi\Filelib\Folder\Folder $folder, $filename);
    
    
}
