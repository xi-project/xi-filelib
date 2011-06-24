<?php

namespace Emerald\Tests\Filelib;

class TestCase extends \Emerald\Tests\TestCase
{
    
    
    public function getMockAcl()
    {
        $acl = $this->getMock('\Emerald\Filelib\Acl\Acl');
        return $acl;
        
    }
    
    
    public function getMockStorage()
    {
        $storage = $this->getMockForAbstractClass('\Emerald\Filelib\Storage\AbstractStorage');
        return $storage;
    }

    
    public function getMockBackend()
    {
        $backend = $this->getMockForAbstractClass('\Emerald\Filelib\Backend\AbstractBackend');
        return $backend;
    }
    
    
    public function getMockPublisher()
    {
        $backend = $this->getMockForAbstractClass('\Emerald\Filelib\Publisher\AbstractPublisher');
        return $backend;
    }
    
    public function getMockConfiguration()
    {
        $configuration = new \Emerald\Filelib\Configuration();
        $configuration->setStorage($this->getMockStorage());
        $configuration->setAcl($this->getMockAcl());
        $configuration->setBackend($this->getMockBackend());
        $configuration->setPublisher($this->getMockPublisher());
        $configuration->setTempDir(ROOT_TESTS . '/data/temp');
        
        return $configuration;
    }
    
    public function getMockFilelib()
    {
        return new \Emerald\Filelib\Filelibrary($this->getMockConfiguration());    
    }
    
    
}