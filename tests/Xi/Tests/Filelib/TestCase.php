<?php

namespace Xi\Tests\Filelib;

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
    
    public function getMockConfiguration()
    {
        $configuration = new \Xi\Filelib\Configuration();
        $configuration->setStorage($this->getMockStorage());
        $configuration->setAcl($this->getMockAcl());
        $configuration->setBackend($this->getMockBackend());
        $configuration->setPublisher($this->getMockPublisher());
        $configuration->setTempDir(ROOT_TESTS . '/data/temp');
        
        return $configuration;
    }
    
    public function getMockFilelib()
    {
        return new \Xi\Filelib\Filelibrary($this->getMockConfiguration());
    }
    
    
}