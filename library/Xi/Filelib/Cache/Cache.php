<?php

namespace Xi\Filelib\Cache;

use Xi\Filelib\Cache\Adapter\CacheAdapter;
use Xi\Filelib\IdentityMap\Identifiable;

class Cache
{
    /**
     * @var CacheAdapter
     */
    private $adapter;

    public function __construct(CacheAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param $id
     * @param $className
     * @return Identifiable
     */
    public function findById($id, $className)
    {
        return $this->adapter->findById($id, $className);
    }

    /**
     * @param array $ids
     * @param $className
     * @return Identifiable[]
     */
    public function findByIds(array $ids = array(), $className)
    {
        return $this->adapter->findByIds($ids, $className);
    }

    /**
     * @param Identifiable[] $identifiables
     */
    public function saveMany($identifiables)
    {
        return $this->adapter->saveMany($identifiables);
    }

    /**
     * @param Identifiable[] $identifiables
     */
    public function deleteMany($identifiables)
    {
        return $this->adapter->deleteMany($identifiables);
    }

    /**
     * @param Identifiable $identifiable
     */
    public function save(Identifiable $identifiable)
    {
        return $this->adapter->save($identifiable);
    }

    /**
     * @param Identifiable $identifiable
     * @return mixed
     */
    public function delete(Identifiable $identifiable)
    {
        return $this->adapter->delete($identifiable);
    }
}


