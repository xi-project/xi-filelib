<?php

namespace Xi\Filelib\Folder;

/**
 * Folder interface
 * 
 * @author pekkis
 *
 */
interface Folder
{

    /**
     * Sets filelib
     *
     * @param \Xi_Filelib $filelib
     */
    public function setFilelib(\Xi\Filelib\FileLibrary $filelib);

    /**
     * Returns filelib
     *
     * @return \Xi\Filelib\FileLibrary Filelib
     */
    public function getFilelib();

    /**
     * Returns the standardized array representation of folder
     * 
     * @return array
     */
    
    public function toArray();
    
    /**
     * Populates object from the standardized array representation
     * 
     * @param array $data
     */
    public function fromArray(array $data);
            
    /**
     * Sets id
     * 
     * @param mixed $id
     */
    public function setId($id);
    
    /**
     * Returns id
     * 
     * @return mixed
     */
    public function getId();
    
    /**
     * Sets parent id
     * 
     * @param mixed $parentId
     */
    public function setParentId($parentId);
    
    /**
     * Returns parent id
     * 
     * @return mixed
     */
    public function getParentId();
    
    /**
     * Sets name
     * 
     * @param string $name
     */
    public function setName($name);
    
    /**
     * Returns name
     * 
     * @return string
     */
    public function getName();
    
    /**
     * Creates a new folder item and populates it with data
     * 
     * @param array $data
     */
    public static function create(array $data);
    
    
}