<?php

namespace Xi\Filelib\Cache;

use Xi\Filelib\IdentityMap\Identifiable;
use Memcached;
use Xi\Filelib\RuntimeException;

class MemcachedCache implements Cache
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


    public function findById($id, $className)
    {
        $ret = $this->memcached->get($this->createKeyFromParts($id, $className));
        return $ret;
    }

    public function findByIds(array $ids = array(), $className)
    {

    }

    public function saveMany($identifiables)
    {
        $arr = array();
        foreach ($identifiables as $identifiable) {
            $arr[$this->createKeyFromIdentifiable($identifiable)] = $identifiable;
        }
        $this->memcached->setMulti($arr);
    }

    public function deleteMany($identifiables)
    {

    }

    public function save(Identifiable $identifiable)
    {
        return $this->memcached->set(
            $this->createKeyFromIdentifiable($identifiable),
            $identifiable
        );
    }

    public function delete(Identifiable $identifiable)
    {

    }

    private function createKeyFromIdentifiable(Identifiable $identifiable)
    {
        if (!$identifiable->getId()) {
            throw new RuntimeException("Identifiable is missing an id");
        }
        return $this->createKeyFromParts($identifiable->getId(), get_class($identifiable));

    }

    private function createKeyFromParts($id, $className)
    {
        return $this->prefix . $className . '___' . $id;
    }

}
