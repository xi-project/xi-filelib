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
    );

    private $id;

    private $hash;

    private $dateCreated;

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
    public static function create(array $data)
    {
        $file = new self();
        return $file->fromArray($data);
    }


}