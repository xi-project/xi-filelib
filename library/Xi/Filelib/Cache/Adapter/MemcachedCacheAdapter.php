<?php

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
        $ret = $this->memcached->get($this->createKeyFromParts($id, $className));
        return $ret;
    }

    /**
     * @param array $ids
     * @param $className
     * @return Identifiable[]
     */
    public function findByIds(array $ids = array(), $className)
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
     * @return bool
     */
    public function save(Identifiable $identifiable)
    {
        return $this->memcached->set(
            $this->createKeyFromIdentifiable($identifiable),
            $identifiable
        );
    }

    public function delete(Identifiable $identifiable)
    {
        return $this->memcached->delete($this->createKeyFromIdentifiable($identifiable));
    }

    public function createKeyFromIdentifiable(Identifiable $identifiable)
    {
        if (!$identifiable->getId()) {
            throw new RuntimeException("Identifiable is missing an id");
        }
        return $this->createKeyFromParts($identifiable->getId(), get_class($identifiable));

    }

    public function createKeyFromParts($id, $className)
    {
        return $this->prefix . $className . '___' . $id;
    }

}
