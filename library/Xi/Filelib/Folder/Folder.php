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

    private $id;

    private $parentId;

    private $name;

    private $url;

    private $uuid;

    /**
     * Sets id
     *
     * @param  type   $id
     * @return Folder
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

    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return Folder
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

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
