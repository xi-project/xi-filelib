<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\IdentityMap;
use Iterator;

/**
 * Identity map
 */
class IdentityMap
{
    /**
     * @var array
     */
    private $objectIdentifiers = array();

    /**
     * @var array
     */
    private $objects = array();

    /**
     * Returns whether identity map has an identifiable
     *
     * @param Identifiable $object
     * @return bool
     */
    public function has(Identifiable $object)
    {
        return isset($this->objectIdentifiers[spl_object_hash($object)]);
    }

    /**
     * Adds an identifiable to identity map
     *
     * @param Identifiable $object
     * @throws IdentityMapException
     * @return bool
     */
    public function add(Identifiable $object)
    {
        if ($this->has($object)) {
            return false;
        }

        if (!$object->getId()) {
            throw new IdentityMapException("Trying to add a file without id to identity map");
        }

        $identifier = $this->getIdentifierFromObject($object);

        $this->objectIdentifiers[spl_object_hash($object)] = $identifier;
        $this->objects[$identifier] = $object;

        return true;
    }

    /**
     * Removes an identifiable
     *
     * @param Identifiable $object
     * @return bool
     */
    public function remove(Identifiable $object)
    {
        $splHash = spl_object_hash($object);

        if (!isset($this->objectIdentifiers[$splHash])) {
            return false;
        }
        unset($this->objects[$this->objectIdentifiers[$splHash]]);
        unset($this->objectIdentifiers[$splHash]);
        return true;
    }

    /**
     * Gets an identifiable by id and class name
     *
     * @param mixed $id
     * @param string $className
     * @return Identifiable|false
     */
    public function get($id, $className)
    {
        $identifier = $this->getIdentifier($id, $className);

        if (!isset($this->objects[$identifier])) {
            return false;
        }

        return $this->objects[$identifier];
    }

    /**
     * @param $id
     * @param $className
     * @return string
     */
    protected function getIdentifier($id, $className)
    {
        return $className . ' ' . $id;
    }

    /**
     * @param Identifiable $object
     * @return string
     */
    protected function getIdentifierFromObject(Identifiable $object)
    {
        return get_class($object) . ' ' . $object->getId();
    }

}
