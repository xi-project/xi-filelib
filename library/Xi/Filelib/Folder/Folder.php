<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder;

use Xi\Filelib\BaseIdentifiable;
use Xi\Filelib\Identifiable;
use Xi\Filelib\IdentifiableDataContainer;

/**
 * Folder
 *
 * @author pekkis
 *
 */
class Folder extends BaseIdentifiable implements Identifiable
{
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
            'data' => $this->getData()->toArray()
        );
    }

    /**
     *
     * @param  array  $data
     * @return Folder
     */
    public static function create(array $data = array())
    {
        $defaults = array(
            'id' => null,
            'parent_id' => null,
            'name' => null,
            'url' => null,
            'uuid' => null,
            'data' => new IdentifiableDataContainer(array())
        );
        $data = array_merge($defaults, $data);

        $obj = new self();
        $obj->setId($data['id']);
        $obj->setParentId($data['parent_id']);
        $obj->setName($data['name']);
        $obj->setUrl($data['url']);
        $obj->setUuid($data['uuid']);
        $obj->setData($data['data']);

        return $obj;
    }
}
