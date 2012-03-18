<?php

namespace Xi\Filelib\Folder;

use \Xi\Filelib\FileLibrary;
use \Xi\Filelib\FilelibException;

/**
 * Default folder implementation
 *
 * @author pekkis
 *
 */
class FolderItem implements Folder
{
    /**
     * Key to method mapping for fromArray
     * 
     * @var array
     */
    protected static $map = array(
        'id' => 'setId',
        'parent_id' => 'setParentId',
        'name' => 'setName',
        'url' => 'setUrl',
    );

    private $id;
    
    private $parentId;
    
    private $name;
    
    private $url;
            
    /**
     * Sets id
     * 
     * @param type $id
     * @return FolderItem 
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }
    
    public function getParentId()
    {
        return $this->parentId;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
    
    
    public function getUrl()
    {
        return $this->url;
    }
    
    
    
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'parent_id' => $this->getParentId(),
            'name' => $this->getName(),
            'url' => $this->getUrl(),
        );
    }
    
    public function fromArray(array $data)
    {
        foreach(static::$map as $key => $method) {
            if(isset($data[$key])) {
                $this->$method($data[$key]);
            }
        }
        return $this;
    }

    /**
     *
     * @param array $data
     * @return type FileItem
     */
    public static function create(array $data)
    {
        $folder = new self();
        return $folder->fromArray($data);
    } 
    
    
    

}
