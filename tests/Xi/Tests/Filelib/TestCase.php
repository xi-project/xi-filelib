<?php

namespace Xi\Tests\Filelib;

use Xi\Filelib\FileLibrary;

class TestCase extends \Xi\Tests\TestCase
{
    
    
    public function getMockAcl()
    {
        $acl = $this->getMock('\Xi\Filelib\Acl\Acl');
        return $acl;
        
    }
    
    
    public function getMockStorage()
    {
        $storage = $this->getMockForAbstractClass('\Xi\Filelib\Storage\AbstractStorage');
        return $storage;
    }

    
    public function getMockBackend()
    {
        $backend = $this->getMockForAbstractClass('\Xi\Filelib\Backend\AbstractBackend');
        return $backend;
    }
    
    
    public function getMockPublisher()
    {
        $backend = $this->getMockForAbstractClass('\Xi\Filelib\Publisher\AbstractPublisher');
        return $backend;
    }
    
    public function getFilelib()
    {
        $filelib = new FileLibrary();
        $filelib->setTempDir(ROOT_TESTS . '/data/temp');
        
        return $filelib;
    }
    
    
}