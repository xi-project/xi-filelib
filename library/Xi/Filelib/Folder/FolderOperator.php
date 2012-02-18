<?php

namespace Xi\Filelib\Folder;

use Xi\Filelib\Folder\Folder;
use ArrayIterator;

interface FolderOperator
{
    /**
     * Returns an instance of the folder class
     * 
     * @param mixed $data Data as array or a folder item instance
     */
    public function getInstance(array $data = array());
    
    /**
     * Creates a folder
     *
     * @param Folder $folder
     */
    public function create(Folder $folder);
    
    /**
     * Deletes a folder
     *
     * @param Folder $folder Folder
     */
    public function delete(Folder $folder);
    
    /**
     * Updates a folder
     *
     * @param Folder $folder Folder
     */
    public function update(Folder $folder);
    
    /**
     * Finds and returns the root folder
     *
     * @return Folder
     */
    public function findRoot();
    
    /**
     * Finds a folder
     *
     * @param mixed $id Folder id
     * @return Folder
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
     * @param Folder $folder Folder
     * @return ArrayIterator
     */
    public function findSubFolders(Folder $folder);
    
    /**
     * Finds parent folder
     * 
     * @param Folder $folder
     * @return Folder|false
     */
    public function findParentFolder(Folder $folder);
    
    /**
     * @param Folder $folder Folder
     * @return ArrayIterator Collection of file items
     */
    public function findFiles(Folder $folder);
    
    /**
     * Sets file item class name
     */
    public function setClass($className);
    
    /**
     * Sets file item class name
     */
    public function getClass();

    
}