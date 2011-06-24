<?php

namespace Xi\Filelib\Storage;

class MultiStorage extends \Xi\Filelib\Storage\AbstractStorage
{
    
    private $_storages;
    
    private $_rand;
    
    public function setStorages(array $storages)
    {
        foreach ($storages as $storage) {
            $storageObj = new $storage['type']($storage['options']);
            $this->addStorage($storageObj);
        }
    }
        
    public function addStorage(\Xi\Filelib\Storage\Storage $storage)
    {
        $this->_storages[] = $storage;
    }
    
    public function getStorages()
    {
        return $this->_storages;
    }
    
    
    public function getRandomStorage()
    {
        // Ensures same random storage inside a single filelib session
        if(!$this->_rand) {
            $this->_rand = array_rand($this->_storages);
        }
        
        return $this->_storages[$this->_rand];
    }
    
    
    
    public function store(Xi_Filelib_FileItem $file, $tempFile)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->store($upload, $file);
        }        
    }
    
    public function storeVersion(Xi_Filelib_FileItem $file, Xi_Filelib_Plugin_VersionProvider_Interface $version, $tempFile)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->storeVersion($file, $version, $tempFile);
        }        
    }
    
    public function retrieve(Xi_Filelib_FileItem $file)
    {
        return $this->getRandomStorage()->retrieve($file);
    }
    
    public function retrieveVersion(Xi_Filelib_FileItem $file, Xi_Filelib_Plugin_VersionProvider_Interface $version)
    {
        return $this->getRandomStorage()->retrieveVersion($file, $version);
    }
    
    public function delete(Xi_Filelib_FileItem $file)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->delete($file);
        }        
    }
    
    public function deleteVersion(Xi_Filelib_FileItem $file, Xi_Filelib_Plugin_VersionProvider_Interface $version)
    {
        foreach ($this->getStorages() as $storage) {
            $storage->deleteVersion($file, $version);
        }        
    }
    
    
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->getRandomStorage(), $method), $args);
    } 
    
}