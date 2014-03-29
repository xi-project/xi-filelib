<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Cache\Adapter;

use Xi\Filelib\IdentityMap\Identifiable;
use Memcached;
use Xi\Filelib\RuntimeException;

class MemcachedCacheAdapter implements CacheAdapter
{
    /**
     * @var Memcached
     */
    private $memcached;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param Memcached $memcached
     */
    public function __construct(Memcached $memcached, $prefix = '')
    {
        $this->memcached = $memcached;
        $this->prefix = $prefix;
    }

    /**
     * @param $id
     * @param $className
     * @return Identifiable
     */
    public function findById($id, $className)
    {
        return $this->memcached->get($this->createKeyFromParts($id, $className));
    }

    /**
     * @param array $ids
     * @param $className
     * @return Identifiable[]
     */
    public function findByIds(array $ids, $className)
    {
        $keys = array();
        foreach ($ids as $id) {
            $keys[] = $this->createKeyFromParts($id, $className);
        }
        return $this->memcached->getMulti($keys);
    }

    /**
     * @param Identifiable[] $identifiables
     */
    public function saveMany($identifiables)
    {
        $arr = array();
        foreach ($identifiables as $identifiable) {
            $arr[$this->createKeyFromIdentifiable($identifiable)] = $identifiable;
        }
        $this->memcached->setMulti($arr);
    }

    /**
     * @param Identifiable[] $identifiables
     */
    public function deleteMany($identifiables)
    {
        $keys = array();
        foreach ($identifiables as $identifiable) {
            $keys[] = $this->createKeyFromIdentifiable($identifiable);
        }

        $this->memcached->deleteMulti($keys);
    }

    /**
     * @param Identifiable $identifiable
     */
    public function save(Identifiable $identifiable)
    {
        $this->memcached->set(
            $this->createKeyFromIdentifiable($identifiable),
            $identifiable
        );
    }

    /**
     * @param Identifiable $identifiable
     */
    public function delete(Identifiable $identifiable)
    {
        $this->memcached->delete($this->createKeyFromIdentifiable($identifiable));
    }

    /**
     * @param Identifiable $identifiable
     * @return string
     * @throws RuntimeException
     */
    public function createKeyFromIdentifiable(Identifiable $identifiable)
    {
        if (!$identifiable->getId()) {
            throw new RuntimeException("Identifiable is missing an id");
        }

        return $this->createKeyFromParts($identifiable->getId(), get_class($identifiable));
    }

    /**
     * @param string $id
     * @param string $className
     * @return string
     */
    public function createKeyFromParts($id, $className)
    {
        return $this->prefix . $className . '___' . $id;
    }
}
