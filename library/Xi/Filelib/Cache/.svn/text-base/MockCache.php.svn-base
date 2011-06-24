<?php

namespace Xi\Filelib\Cache;

class MockCache extends AbstractCache
{
    public function _load($id)
    {
        return false;
    }
        
    public function _save($id, $data)
    {
        return true;    
    }
    
    public function _remove($id)
    {
        return true;
    }
    
    public function _contains($id)
    {
        return false;
    }
        
}

