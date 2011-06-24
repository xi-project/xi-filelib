<?php

namespace Xi\Filelib\Cache\Adapter;

class ZendCacheAdapter extends AbstractCacheAdapter
{

    protected function _load($id)
    {
        return $this->getCache()->load($id);
    }
        
    protected function _save($id, $data)
    {
        return $this->getCache()->save($data, $id);
    }

    
    protected function _contains($id)
    {
        return (bool) $this->_load($id);
    }
    
    
    protected function _remove($id)
    {
        return $this->getCache()->remove($id);
    }
    
    
}