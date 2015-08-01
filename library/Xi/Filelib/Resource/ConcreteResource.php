<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Resource;

use DateTime;
use Pekkis\DirectoryCalculator\Dateable;
use Pekkis\DirectoryCalculator\UniversallyIdentifiable;
use Xi\Filelib\BaseIdentifiable;
use Xi\Filelib\Identifiable;
use Xi\Filelib\IdentifiableDataContainer;
use Xi\Filelib\Versionable\Versionable;

class ConcreteResource extends BaseIdentifiable implements Identifiable, UniversallyIdentifiable, Dateable
{
    /**
     * @var string
     */
    private $hash;

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
     * @return ConcreteResource
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
     * @return ConcreteResource
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
     * @return ConcreteResource
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
     * @return ConcreteResource
     */
    public function setSize($size)
    {
        $this->size = $size;

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
            'uuid' => $this->getUuid(),
            'hash' => $this->getHash(),
            'date_created' => $this->getDateCreated(),
            'data' => $this->getData()->toArray(),
            'mimetype' => $this->getMimetype(),
            'size' => $this->getSize(),
        );
    }


    /**
     * Creates an instance with data
     *
     * @param  array    $data
     * @return ConcreteResource
     */
    public static function create(array $data = array())
    {
        $defaults = array(
            'id' => null,
            'uuid' => null,
            'hash' => null,
            'date_created' => new DateTime(),
            'data' => new IdentifiableDataContainer(array()),
            'mimetype' => null,
            'size' => null,
            'exclusive' => false
        );
        $data = array_merge($defaults, $data);

        $obj = new self();
        $obj->setUuid($data['uuid']);
        $obj->setId($data['id']);
        $obj->setHash($data['hash']);
        $obj->setDateCreated($data['date_created']);
        $obj->setData($data['data']);
        $obj->setMimetype($data['mimetype']);
        $obj->setSize($data['size']);

        return $obj;
    }
}
