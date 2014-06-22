<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Resource;

use DateTime;
use Xi\Filelib\BaseVersionable;
use Xi\Filelib\Identifiable;
use Xi\Filelib\IdentifiableDataContainer;
use Xi\Filelib\Storage\Versionable;

/**
 * Resource
 */
class Resource extends BaseVersionable implements Identifiable, Versionable
{
    /**
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
            'data' => $this->getData()->toArray(),
            'mimetype' => $this->getMimetype(),
            'size' => $this->getSize(),
            'exclusive' => $this->isExclusive(),
        );
    }


    /**
     * Creates an instance with data
     *
     * @param  array    $data
     * @return Resource
     */
    public static function create(array $data = array())
    {
        if (isset($data['versions'])) {
            throw new \Exception('Gaa gaa');
        }

        $defaults = array(
            'id' => null,
            'hash' => null,
            'date_created' => new DateTime(),
            'data' => new IdentifiableDataContainer(array()),
            'mimetype' => null,
            'size' => null,
            'exclusive' => false
        );
        $data = array_merge($defaults, $data);

        $obj = new self();
        $obj->setId($data['id']);
        $obj->setHash($data['hash']);
        $obj->setDateCreated($data['date_created']);
        $obj->setData($data['data']);
        $obj->setMimetype($data['mimetype']);
        $obj->setSize($data['size']);
        $obj->setExclusive($data['exclusive']);

        return $obj;
    }
}
