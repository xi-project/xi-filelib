<?php

namespace Xi\Tests\Filelib;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    protected $configuration;
    
    public function setUp()
    {
        $this->configuration = new \Xi\Filelib\Configuration();
    }
    
    
    
    public function testBackendGetAndSet()
    {
        $this->assertNull($this->configuration->getBackend());

        $backend = new \Xi\Filelib\Backend\ZendDbBackend();

        $this->configuration->setBackend($backend);
        
        $this->assertEquals($backend, $this->configuration->getBackend());
        
                
    }
    
    
    public function testStorageGetAndSet()
    {
        $this->assertNull($this->configuration->getStorage());

        $storage = new \Xi\Filelib\Storage\FilesystemStorage();

        $this->configuration->setStorage($storage);
        
        $this->assertEquals($storage, $this->configuration->getStorage());
        
                
    }
    
    
    
    
    
    
}
