<?php

namespace Xi\Filelib\File;

use DateTime;

/**
 * File interface
 * 
 * @author pekkis
 *
 */
interface File
{
    
    const STATUS_RAW = 1;
    const STATUS_UPLOADED = 2;
    
    
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
     * 
     */
    public function getId();
    
    /**
     * Sets folder id
     * 
     * @param mixed $folderId
     */
    public function setFolderId($folderId);
    
    /**
     * Returns folder id
     * 
     * @return mixed
     */
    public function getFolderId();

    /**
     * Sets mime type
     * 
     * @param string $mimetype
     */
    public function setMimetype($mimetype);
    
    /**
     * Returns mime type
     * 
     * @return string
     */
    public function getMimetype();
    
    /**
     * Sets profile name
     * 
     * @param string $profile
     */
    public function setProfile($profile);
    
    /**
     * Returns profile name
     * 
     * @return string
     */
    public function getProfile();
    
    /**
     * Sets size
     * 
     * @param number $size
     */
    public function setSize($size);
    
    /**
     * Returns size
     * 
     * @return number
     */
    public function getSize();
    
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
     * Sets base link
     * 
     * @param string $link
     */
    public function setLink($link);
    
    /**
     * Returns base link
     * 
     * @return string
     */
    public function getLink();
    
    /**
     * Sets upload datetime
     * 
     * @param DateTime $uploadDate
     */
    public function setDateUploaded(DateTime $uploadDate);

    /**
     * Returns upload datetime
     * 
     * @return DateTime
     */
    public function getDateUploaded();
    
    
    /**
     * Sets status
     * 
     * @return File
     */
    public function setStatus($status);
    
    /**
     * Returns status
     * 
     * @return integer
     */
    public function getStatus();

    /**
     * Returns the standardized array representation of a file
     */
    public function toArray();
    
    /**
     * Populates object data from the standard array representation
     * 
     * @param array $data Standardized array representation of file
     */
    public function fromArray(array $data);
        
    /**
     * Creates a new file item and populates it with data
     * 
     * @param array $data
     */
    public static function create(array $data);
    
    
}