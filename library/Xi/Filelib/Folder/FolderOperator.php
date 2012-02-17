<?php

namespace Xi\Filelib\Folder;

interface FolderOperator
{
    /**
     * Returns an instance of the folder class
     * 
     * @param mixed $data Data as array or a folder item instance
     */
    public function getInstance($data = null);
    
    /**
     * Creates a folder
     *
     * @param \Xi\Filelib\Folder\Folder $folder
     * @return unknown_type
     */
    public function create(\Xi\Filelib\Folder\Folder $folder);
    
    /**
     * Deletes a folder
     *
     * @param \Xi\Filelib\Folder\Folder $folder Folder
     */
    public function delete(\Xi\Filelib\Folder\Folder $folder);
    
    /**
     * Updates a folder
     *
     * @param \Xi\Filelib\Folder\Folder $folder Folder
     */
    public function update(\Xi\Filelib\Folder\Folder $folder);
    
    /**
     * Finds and returns the root folder
     *
     * @return \Xi\Filelib\Folder\Folder
     */
    public function findRoot();
    
    /**
     * Finds a folder
     *
     * @param mixed $id Folder id
     * @return \Xi\Filelib\Folder\Folder
     */
    public function find($id);
    
    /**
     * Finds folder by url
     * 
     * @param string $url
     */
    public function findByUrl($url);
    
    /**
     * Finds subfolders
     *
     * @param \Xi_Fildlib_FolderItem $folder Folder
     * @return \ArrayIterator
     */
    public function findSubFolders(\Xi\Filelib\Folder\Folder $folder);
    
    /**
     * Finds parent folder
     * 
     * @param \Xi\Filelib\Folder\Folder $folder
     * @return false|\Xi\Filelib\Folder\Folder
     */
    public function findParentFolder(\Xi\Filelib\Folder\Folder $folder);
    
    /**
     * @param \Xi\Filelib\Folder\Folder $folder Folder
     * @return \ArrayIterator Collection of file items
     */
    public function findFiles(\Xi\Filelib\Folder\Folder $folder);
        
    
    /**
     * Sets file item class name
     */
    public function setClass($className);
    
    /**
     * Sets file item class name
     */
    public function getClass();

    
}