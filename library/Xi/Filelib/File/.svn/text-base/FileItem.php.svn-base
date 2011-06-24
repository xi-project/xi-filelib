<?php

namespace Xi\Filelib\File;

/**
 * Default file implementation
 *
 * @author pekkis
 *
 */
class FileItem implements File
{
    /**
     * Key to method mapping for fromArray
     * 
     * @var array
     */
    protected static $_map = array(
        'id' => 'setId',
        'folder_id' => 'setFolderId',
        'mimetype' => 'setMimeType',
        'profile' => 'setProfile',
        'size' => 'setSize',
        'name' => 'setName',
        'link' => 'setLink',
        'date_uploaded' => 'setDateUploaded'
    );
        
    /**
     * @var \Xi\Filelib\FileLibrary Filelib
     */
    private $_filelib;

    private $_profileObj;
    
    private $_id;
    
    private $_folderId;
    
    private $_mimetype;
    
    private $_profile;
    
    private $_size;
    
    private $_name;
    
    private $_link;
    
    private $_dateUploaded;
    
    /**
     * Sets filelib
     *
     * @param \Xi_Filelib $filelib
     */
    public function setFilelib(\Xi\Filelib\FileLibrary $filelib)
    {
        $this->_filelib = $filelib;
    }

    /**
     * Returns filelib
     *
     * @return \Xi\Filelib\FileLibrary
     */
    public function getFilelib()
    {
        return $this->_filelib;
    }
    
    public function setId($id)
    {
        $this->_id = $id;
    }
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function setFolderId($folderId)
    {
        $this->_folderId = $folderId;
    }
    
    public function getFolderId()
    {
        return $this->_folderId;
    }

    public function setMimetype($mimetype)
    {
        $this->_mimetype = $mimetype;
    }
    
    public function getMimetype()
    {
        return $this->_mimetype;
    }
    
    public function setProfile($profile)
    {
        $this->_profile = $profile;        
    }
    
    public function getProfile()
    {
        return $this->_profile;
    }
    
    public function setSize($size)
    {
        $this->_size = $size;
    }
    
    public function getSize()
    {
        return $this->_size;
    }
    
    public function setName($name)
    {
        $this->_name = $name;
    }
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function setLink($link)
    {
        $this->_link = $link;
    }
    
    public function getLink()
    {
        return $this->_link;
    }
    
    public function getProfileObject()
    {
        return $this->getFilelib()->file()->getProfile($this->getProfile());
    }
    
    public function getDateUploaded()
    {
        return $this->_dateUploaded;
    }
    
    public function setDateUploaded(\DateTime $dateUploaded)
    {
        $this->_dateUploaded = $dateUploaded;
    }
    
    
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'folder_id' => $this->getFolderId(),
            'mimetype' => $this->getMimetype(),
            'profile' => $this->getProfile(),
            'size' => $this->getSize(),
            'name' => $this->getName(),
            'link' => $this->getLink(),
            'date_uploaded' => $this->getDateUploaded(),
        );
    }
    
    public function fromArray(array $data)
    {
        foreach(static::$_map as $key => $method) {
            if(isset($data[$key])) {
                $this->$method($data[$key]);
            }
        }
        return $this;
    }

    public static function create(array $data)
    {
        $file = new self();
        return $file->fromArray($data);
    } 

}
