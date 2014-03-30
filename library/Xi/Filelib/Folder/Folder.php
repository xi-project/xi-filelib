<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder;

use Xi\Filelib\IdentityMap\Identifiable;

/**
 * Folder
 *
 * @author pekkis
 *
 */
class Folder implements Identifiable
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
        'uuid' => 'setUuid',
    );

    /**
     * @var mixed
     */
    private $id;

    /**
     * @var mixed
     */
    private $parentId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $uuid;

    private function __construct()
    { }

    /**
     * Sets id
     *
     * @param mixed $id
     * @return Folder
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
     *
     * @param  mixed  $parentId
     * @return Folder
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     *
     * @param  string $name
     * @return Folder
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param  string $url
     * @return Folder
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $uuid
     * @return $this
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'parent_id' => $this->getParentId(),
            'name' => $this->getName(),
            'url' => $this->getUrl(),
            'uuid' => $this->getUuid(),
        );
    }

    /**
     *
     * @param  array  $data
     * @return Folder
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
     *
     * @param  array  $data
     * @return Folder
     */
    public static function create(array $data = array())
    {
        $folder = new self();
        return $folder->fromArray($data);
    }
}
