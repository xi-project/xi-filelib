<?php

namespace Xi\Filelib\Cache;

abstract class AbstractCache implements Cache
{
    private $_autoSerialize = false;
    
    abstract protected function _save($id, $data);
    
    abstract protected function _load($id);
    
    abstract protected function _remove($id);

    abstract protected function _contains($id);
    
    public function setAutoSerialize($autoSerialize)
    {
        $this->_autoSerialize = (bool) $autoSerialize;
    }
    
    public function getAutoSerialize()
    {
        return $this->_autoSerialize;
    }

    public function save($id, $data)
    {
        return $this->_save($id, $data);        
    }
    
    public function load($id)
    {
        return $this->_load($id);
    }
    
    public function remove($id)
    {
        return $this->_remove($id);
    }
    
    public function contains($id)
    {
        return $this->_contains($id);
    }
    
}