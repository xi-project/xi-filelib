<?php

namespace Xi\Filelib\Cache;

interface Cache
{
    public function load($id);
        
    public function save($id, $data);

    public function remove($id);
    
    public function contains($id);
    
}