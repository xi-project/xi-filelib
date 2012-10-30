<?php

namespace Xi\Filelib\File;

use DateTime;

/**
 * Resource
 */
class Resource
{
    /**
     * Key to method mapping for fromArray
     *
     * @var array
     */
    protected static $map = array(
        'id' => 'setId',
        'hash' => 'setHash',
        'date_created' => 'setDateCreated',
        'versions' => 'setVersions',
        'mimetype' => 'setMimetype',
        'size' => 'setSize',
        'exclusive' => 'setExclusive',
    );

    /**
     *
     * @var mixed
     */
    private $id;

    /**
     *
     * @var string
     */
    private $hash;

    /**
     * @var boolean
     */
    private $exclusive = false;

    /**
     *
     * @var DateTime
     */
    private $dateCreated;

    /**
     *
     * @var string
     */
    private $mimetype;

    /**
     *
     * @var integer
     */
    private $size;

    /**
     *
     * @var array
     */
    private $versions = array();

    /**
     * Sets id
     *
     * @param mixed $id
     * @return Resource
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
     *
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets create datetime
     *
     * @param DateTime $dateCreated
     * @return Resource
     */
    public function setDateCreated(DateTime $dateCreated)
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    /**
     * Returns create datetime
     *
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param string $hash
     * @return Resource
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     *
     * @return string
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }

    /**
     *
     * @param string $mimetype
     * @return Resource
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     *
     * @param integer $size
     * @return Resource
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Returns whether resource is marked as exclusive
     *
     * @return boolean
     */
    public function isExclusive()
    {
        return $this->exclusive;
    }

    /**
     * Sets resource as exclusive or non exclusive
     *
     * @param boolean $exclusive
     * @return Resource
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;
        return $this;
    }

    /**
     * Sets currently created versions
     *
     * @param array $versions
     * @return Resource
     */
    public function setVersions(array $versions = array())
    {
        $this->versions = $versions;
        return $this;
    }

    /**
     * Returns currently created versions
     *
     * @return array
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * Adds a version
     *
     * @param string $version
     */
    public function addVersion($version)
    {
        if (!in_array($version, $this->versions)) {
            $this->versions[] = $version;
        }
    }

    /**
     * Removes a version
     *
     * @param string $version
     */
    public function removeVersion($version)
    {
        $this->versions = array_diff($this->versions, array($version));
    }

    /**
     * Returns whether resource has version
     *
     * @param string $version
     * @return boolean
     */
    public function hasVersion($version)
    {
        return in_array($version, $this->versions);
    }

    /**
     * Returns the resource as array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'hash' => $this->getHash(),
            'date_created' => $this->getDateCreated(),
            'versions' => $this->getVersions(),
            'mimetype' => $this->getMimetype(),
            'size' => $this->getSize(),
            'exclusive' => $this->isExclusive(),
        );
    }

    /**
     * Sets data from array
     *
     * @param array $data
     * @return Resource
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
     * @return Resource
     */
    public static function create(array $data = array())
    {
        $file = new self();
        return $file->fromArray($data);
    }
}
