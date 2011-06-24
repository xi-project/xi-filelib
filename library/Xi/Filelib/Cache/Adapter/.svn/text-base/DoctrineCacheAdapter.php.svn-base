<?php

namespace Xi\Filelib\Cache\Adapter;

class DoctrineCacheAdapter extends AbstractCacheAdapter
{

    protected function _load($id)
    {
        return $this->getCache()->fetch($id);
    }
        
    protected function _save($id, $data)
    {
        return $this->getCache()->save($id, $data);
    }

    
    protected function _contains($id)
    {
        return $this->getCache()->contains($id);
    }
    
    
    protected function _remove($id)
    {
        return $this->getCache()->delete($id);
    }
    
    
}