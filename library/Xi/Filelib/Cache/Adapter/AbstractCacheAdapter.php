<?php

namespace Xi\Filelib\Cache\Adapter;

use \Xi\Filelib\Cache\AbstractCache;

abstract class AbstractCacheAdapter extends AbstractCache 
{
    private $_cache;
    
    public function setCache($cache)
    {
        $this->_cache = $cache;
    }
    
    public function getCache()
    {
        return $this->_cache;
    }
    
}