<?php

namespace Xi\Filelib\Cache;

class MockCache extends AbstractCache
{
    protected function _load($id)
    {
        return false;
    }
        
    protected function _save($id, $data)
    {
        return true;    
    }
    
    protected function _remove($id)
    {
        return true;
    }
    
    public function _contains($id)
    {
        return false;
    }
        
}

