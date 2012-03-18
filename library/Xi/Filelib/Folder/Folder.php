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
     * Sets url
     * 
     * @param string $url
     */
    public function setUrl($url);
    
    /**
     * Returns url
     * 
     * @return string
     */
    public function getUrl();
    
    
    /**
     * Creates a new folder item and populates it with data
     * 
     * @param array $data
     */
    public static function create(array $data);
    
    
}