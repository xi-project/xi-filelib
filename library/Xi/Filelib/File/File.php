<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File;

use DateTime;
use Pekkis\DirectoryCalculator\Dateable;
use Pekkis\DirectoryCalculator\UniversallyIdentifiable;
use Xi\Filelib\Versionable\BaseVersionable;
use Xi\Filelib\Identifiable;
use Xi\Filelib\IdentifiableDataContainer;
use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Versionable\Versionable;

class File extends BaseVersionable implements Identifiable, Versionable, UniversallyIdentifiable, Dateable
{
    const STATUS_RAW = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_DELETED = 3;

    /**
     * @var mixed
     */
    private $folderId;

    /**
     * @var string
     */
    private $profile;

    /**
     * @var string
     */
    private $name;

    /**
     * @var DateTime
     */
    private $dateCreated;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * Sets folder id
     *
     * @param  mixed $folderId
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
     * Returns mimetype
     *
     * @return string
     */
    public function getMimetype()
    {
        return $this->getResource()->getMimetype();
    }

    /**
     * Sets profile name
     *
     * @param  string $profile
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
     * Returns file size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->getResource()->getSize();
    }

    /**
     * Sets name
     *
     * @param  string $name
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
     * Returns create date
     *
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Sets create date
     *
     * @param  DateTime $dateCreated
     * @return File
     */
    public function setDateCreated(DateTime $dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Sets status
     *
     * @param  integer $status
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
     *
     * @param  ConcreteResource $resource
     * @return File
     */
    public function setResource(ConcreteResource $resource = null)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Returns resource or null if file doesn't have one
     *
     * @return ConcreteResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Unsets resource
     */
    public function unsetResource()
    {
        $this->resource = null;
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
            'profile' => $this->getProfile(),
            'name' => $this->getName(),
            'date_created' => $this->getDateCreated(),
            'status' => $this->getStatus(),
            'resource' => $this->getResource(),
            'uuid' => $this->getUuid(),
            'data' => $this->getData()->toArray(),
        );
    }

    /**
     * Creates an instance with data
     *
     * @param  array $data
     * @return self
     */
    public static function create(array $data = array())
    {
        $defaults = array(
            'id' => null,
            'folder_id' => null,
            'profile' => null,
            'name' => null,
            'date_created' => new DateTime(),
            'status' => null,
            'resource' => null,
            'uuid' => null,
            'data' => new IdentifiableDataContainer(array())
        );
        $data = array_merge($defaults, $data);

        $obj = new self();
        $obj->setId($data['id']);
        $obj->setFolderId($data['folder_id']);
        $obj->setProfile($data['profile']);
        $obj->setName($data['name']);
        $obj->setDateCreated($data['date_created']);
        $obj->setStatus($data['status']);
        $obj->setResource($data['resource']);
        $obj->setUuid($data['uuid']);
        $obj->setData($data['data']);
        return $obj;
    }
}
