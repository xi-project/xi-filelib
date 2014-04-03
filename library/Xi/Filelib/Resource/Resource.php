<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Resource;

use DateTime;
use Xi\Filelib\Identifiable;
use ArrayObject;

/**
 * Resource
 */
class Resource implements Identifiable
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
        'data' => 'setData',
        'mimetype' => 'setMimetype',
        'size' => 'setSize',
        'exclusive' => 'setExclusive',
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
     * @var boolean
     */
    private $exclusive = false;

    /**
     * @var DateTime
     */
    private $dateCreated;

    /**
     * @var string
     */
    private $mimetype;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var ArrayObject
     */
    private $data;

    private function __construct()
    {
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

    /**
     * @param mixed $data
     * @return Resource
     */
    public function setData($data)
    {
        if (is_array($data)) {
            $data = new ArrayObject($data);
        }

        $this->data = $data;
        return $this;
    }

    /**
     * Sets id
     *
     * @param  mixed    $id
     * @return Resource
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets create datetime
     *
     * @param  DateTime $dateCreated
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
     * @param  string   $hash
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
     * @param  string   $mimetype
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
     * @param  integer  $size
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
     * @param  boolean  $exclusive
     * @return Resource
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;

        return $this;
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
            'data' => $this->getData(),
            'mimetype' => $this->getMimetype(),
            'size' => $this->getSize(),
            'exclusive' => $this->isExclusive(),
        );
    }

    /**
     * Sets data from array
     *
     * @param  array    $data
     * @return Resource
     */
    public function fromArray(array $data)
    {
        foreach (static::$map as $key => $method) {
            if (isset($data[$key])) {
                $this->$method($data[$key]);
            }
        }

        return $this;
    }

    /**
     * Creates an instance with data
     *
     * @param  array    $data
     * @return Resource
     */
    public static function create(array $data = array())
    {
        $resource = new self();
        return $resource->fromArray($data);
    }


    /**
     * Sets currently created versions
     *
     * @param  array    $versions
     * @return Resource
     */
    public function setVersions(array $versions = array())
    {
        $data = $this->getData();
        $data['versions'] = $versions;

        return $this;
    }

    /**
     * Returns currently created versions
     *
     * @return array
     */
    public function getVersions()
    {
        $data = $this->getData();
        if (!isset($data['versions'])) {
            $data['versions'] = array();
        }
        return $data['versions'];
    }

    /**
     * Adds version
     *
     * @param string $version
     */
    public function addVersion($version)
    {
        $versions = $this->getVersions();
        if (!in_array($version, $versions)) {
            array_push($versions, $version);
            $this->setVersions($versions);
        }
    }

    /**
     * Removes a version
     *
     * @param string $version
     */
    public function removeVersion($version)
    {
        $versions = $this->getVersions();
        $versions = array_diff($versions, array($version));
        $this->setVersions($versions);
    }

    /**
     * Returns whether resource has version
     *
     * @param  string  $version
     * @return boolean
     */
    public function hasVersion($version)
    {
        return in_array($version, $this->getVersions());
    }
}
