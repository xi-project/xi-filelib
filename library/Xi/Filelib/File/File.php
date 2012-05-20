<?php

namespace Xi\Filelib\File;

use DateTime;
use ArrayObject;

/**
 * File
 */
class File
{
    const STATUS_RAW = 1;
    const STATUS_UPLOADED = 2;

    /**
     * Key to method mapping for fromArray
     *
     * @var array
     */
    protected static $map = array(
        'id' => 'setId',
        'folder_id' => 'setFolderId',
        'mimetype' => 'setMimeType',
        'profile' => 'setProfile',
        'size' => 'setSize',
        'name' => 'setName',
        'link' => 'setLink',
        'date_uploaded' => 'setDateUploaded',
        'status' => 'setStatus',
    );

    /**
     * @var FileLibrary Filelib
     */
    private $filelib;

    private $id;

    private $folderId;

    private $mimetype;

    private $profile;

    private $size;

    private $name;

    private $link;

    private $dateUploaded;

    private $status;

    /**
     *
     * @var ArrayObject
     */
    private $data;


    /**
     * Sets id
     *
     * @param mixed $id
     * @return File
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets folder id
     *
     * @param mixed $folderId
     * @return File
     */
    public function setFolderId($folderId)
    {
        $this->folderId = $folderId;
        return $this;
    }

    /**
     * Returns folder id
     *
     * @return mixed
     */
    public function getFolderId()
    {
        return $this->folderId;
    }

    /**
     * Sets mimetype
     *
     * @param string $mimetype
     * @return File
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;
        return $this;
    }

    /**
     * Returns mimetype
     *
     * @return string
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }

    /**
     * Sets profile name
     *
     * @param string $profile
     * @return File
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Returns profile name
     *
     * @return string
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Sets file size
     *
     * @param int $size
     * @return File
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Returns file size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Sets name
     *
     * @param string $name
     * @return File
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Returns name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets link
     *
     * @param string $link
     * @return File
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    /**
     * Returns link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Returns upload date
     *
     * @return DateTime
     */
    public function getDateUploaded()
    {
        return $this->dateUploaded;
    }

    /**
     * Sets upload date
     *
     * @param DateTime $dateUploaded
     * @return File
     */
    public function setDateUploaded(DateTime $dateUploaded)
    {
        $this->dateUploaded = $dateUploaded;
        return $this;
    }

    /**
     * Sets status
     *
     * @param integer $status
     * @return File
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Returns status
     *
     * @return integer
     */
    public function getStatus()
    {
       return $this->status;
    }

    /**
     * Returns the file as standardized file array
     *
     * @return array
     */
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
            'status' => $this->getStatus(),
        );
    }

    /**
     * Sets data from array
     *
     * @param array $data
     * @return File
     */
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
     * Creates an instance with data
     *
     * @param array $data
     * @return type File
     */
    public static function create(array $data)
    {
        $file = new self();
        return $file->fromArray($data);
    }

    /**
     * @return ArrayObject
     */
    public function getData()
    {
        if (!$this->data) {
            $this->data = new ArrayObject();
        }
        return $this->data;
    }

}
