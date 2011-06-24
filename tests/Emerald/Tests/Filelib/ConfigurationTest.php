<?php

namespace Emerald\Tests\Filelib;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    protected $configuration;
    
    public function setUp()
    {
        $this->configuration = new \Emerald\Filelib\Configuration();  
    }
    
    
    
    public function testBackendGetAndSet()
    {
        $this->assertNull($this->configuration->getBackend());

        $backend = new \Emerald\Filelib\Backend\ZendDbBackend();

        $this->configuration->setBackend($backend);
        
        $this->assertEquals($backend, $this->configuration->getBackend());
        
                
    }
    
    
    public function testStorageGetAndSet()
    {
        $this->assertNull($this->configuration->getStorage());

        $storage = new \Emerald\Filelib\Storage\FilesystemStorage();

        $this->configuration->setStorage($storage);
        
        $this->assertEquals($storage, $this->configuration->getStorage());
        
                
    }
    
    
    
    
    
    
}
