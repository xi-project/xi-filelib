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
     *
     * @var DateTime
     */
    private $dateCreated;

    /**
     *
     * @var array
     */
    private $versions = array();

    /**
     * Sets id
     *
     * @param mixed $id
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
     * @param string $version
     */
    public function addVersion($version)
    {
        if (!in_array($version, $this->versions)) {
            array_push($this->versions, $version);
        }
    }

    /**
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
     * Returns the resource as standardized hash array
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