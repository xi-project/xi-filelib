<?php

namespace Xi\Filelib;

use \Xi\Filelib\Cache;
use \Xi\Filelib\Folder\FolderOperator;
use \Xi\Filelib\Folder\FileOperator;

/**
 * Xi filelib
 *
 * @author pekkis
 *
 */
class FileLibrary
{
    /**
     * @var \Xi\Filelib\Backend\Backend Backend
     */
    private $_backend;

    /**
     * @var \Xi\Filelib\Storage\Storage Storage
     */
    private $_storage;

    /**
     * @var \Xi\Filelib\Publisher\Publisher Publisher
     */
    private $_publisher;
    
    /**
     * @var \Xi\Filelib\Acl\Acl Acl handler
     */
    private $_acl;

    /**
     * File operator
     * @var \Xi\Filelib\File\FileOperator
     */
    private $_fileOperator;

    /**
     * Folder operator
     * @var \Xi\Filelib\Folder\FolderOperator
     */
    private $_folderOperator;
    
    /**
     * Cache
     * @var \Xi\Filelib\Cache\Cache
     */
    private $_cache;
        
    /**
     * Temporary directory
     * 
     * @var string
     */
    private $_tempDir;
    
    
    public function __construct()
    {
        // $this->_folderOperator = new Folder\FolderOperator($this);
        // $this->_fileOperator = new File\FileOperator($this);
    }
    
    /**
     * Sets temporary directory
     * 
     * @param string $tempDir
     */
    public function setTempDir($tempDir)
    {
        $this->_tempDir = $tempDir;
    }
    
    /**
     * Returns temporary directory
     * 
     * @return string
     */
    public function getTempDir()
    {
        if (!$this->_tempDir) {
            $this->setTempDir(sys_get_temp_dir());
        }
        
        return $this->_tempDir;
        
    }
    
    /**
     * Sets cache
     * 
     * @param \Xi\Filelib\Cache\Cache $cache
     * @return \Xi\Filelib\FileLibrary
     */
    public function setCache(Cache\Cache $cache)
    {
        $this->_cache = $cache;
        return $this;
    }

    /**
     * Returns cache. If cache does not exist, init a mock cache
     * 
     * @return \Xi\Filelib\Cache\Cache
     */
    public function getCache()
    {
        if(!$this->_cache) {
            $this->_cache = new Cache\MockCache();
        }
        return $this->_cache;
    }

    /**
     * Shortcut to getFileOperator
     * 
     * @return \Xi\Filelib\File\FileOperator
     */
    public function file()
    {
        return $this->getFileOperator();
    }

    /**
     * Shortcut to getFolderOperator
     * 
     * @return \Xi\Filelib\Folder\FolderOperator
     */
    public function folder()
    {
        return $this->getFolderOperator();
    }

    
    /**
     * Sets file operator
     * 
     * @param \Xi\Filelib\File\FileOperator $fileOperator 
     */
    public function setFileOperator(FileOperator $fileOperator)
    {
        $this->_fileOperator = $fileOperator;
    }
    
    /**
     * Sets folder operator
     * 
     * @param \Xi\Filelib\Folder\FolderOperator $fileOperator 
     */
    public function setFolderOperator(FolderOperator $folderOperator)
    {
        $this->_folderOperator = $folderOperator;
    }
        
    /**
     * Returns file operator
     * 
     * @return \Xi\Filelib\File\FileOperator
     */
    public function getFileOperator()
    {
        if (!$this->_fileOperator) {
            $this->_fileOperator = new File\DefaultFileOperator($this);
        }
        
        return $this->_fileOperator;
    }

    
    
    /**
     * Returns folder operator
     * 
     * @return Xi\Filelib\Folder\FolderOperator 
     */
    public function getFolderOperator()
    {
        if (!$this->_folderOperator) {
            $this->_folderOperator = new Folder\DefaultFolderOperator($this);
        }
        
        return $this->_folderOperator;
    }
    
    
        
    
    
    /**
     * Sets fully qualified fileitem classname
     *
     * @param string $fileItemClass Class name
     * @return \Xi\Filelib\FileLibrary
     */
    public function setFileItemClass($fileItemClass)
    {
        $this->file()->setClass($fileItemClass);
        return $this;
    }
    
    /**
     * Sets fully qualified folderitem classname
     *
     * @param string $folderItemClass Class name
     * @return \Xi\Filelib\FileLibrary
     */
    public function setFolderItemClass($folderItemClass)
    {
        $this->folder()->setClass($folderItemClass);
        return $this;
    }
    
    
    /**
     * Returns fully qualified folderitem classname
     * 
     * @return string
     */
    public function getFolderItemClass()
    {
        return $this->folder()->getClass();
    }

    /**
     * Returns fully qualified fileitem classname
     * 
     * @return string
     */
    public function getFileItemClass()
    {
        return $this->file()->getClass();
    }
    
    /**
     * Sets storage
     *
     * @param \Xi\Filelib\Storage\Storage $storage
     * @return \Xi\Filelib\FileLibrary
     */
    public function setStorage(Storage\Storage $storage)
    {
        $storage->setFilelib($this);
        $this->_storage = $storage;
        return $this;
    }

    /**
     * Returns storage
     *
     * @return \Xi\Filelib\Storage\Storage
     */
    public function getStorage()
    {
        return $this->_storage;
    }
    
    /**
     * Sets publisher
     *
     * @param \Xi\Filelib\Publisher\Interface $publisher
     * @return \Xi\Filelib\FileLibrary
     */
    public function setPublisher(Publisher\Publisher $publisher)
    {
        $publisher->setFilelib($this);
        $this->_publisher = $publisher;
        return $this;
    }

    /**
     * Returns publisher
     *
     * @return \Xi\Filelib\Publisher\Publisher
     */
    public function getPublisher()
    {
        return $this->_publisher;
    }

    /**
     * Sets backend
     *
     * @param \Xi\Filelib\Backend\Backend $backend
     * @return \Xi\Filelib\FileLibrary
     */
    public function setBackend(Backend\Backend $backend)
    {
        $backend->setFilelib($this);
        $backend->init();
        $this->_backend = $backend;
        return $this;
    }

    /**
     * Returns backend
     *
     * @return \Xi\Filelib\Backend\Backend
     */
    public function getBackend()
    {
        return $this->_backend;
    }

    /**
     * Sets acl handler
     *
     * @param \Xi\Filelib\Acl\Acl $acl
     * @return \Xi\Filelib\FileLibrary Filelib
     */
    public function setAcl(Acl\Acl $acl)
    {
        $this->_acl = $acl;
        return $this;
    }

    /**
     * Returns acl handler
     *
     * @return \Xi\Filelib\Acl\Acl
     */
    public function getAcl()
    {
        return $this->_acl;
    }
    
    /**
     * Adds a file profile
     * 
     * @param File\FileProfile $profile
     */
    public function addProfile(File\FileProfile $profile)
    {
        $this->file()->addProfile($profile);
    }
    
    /**
     * Returns all file profiles
     * 
     * @return array
     */
    public function getProfiles()
    {
        return $this->file()->getProfiles();
    }
    
    /**
     * Adds a plugin
     *
     * @param \Xi\Filelib\Plugin\Plugin Plugin $plugin
     * @return \Xi\Filelib\FileLibrary
     */
    public function addPlugin(Plugin\Plugin $plugin, $priority = 1000)
    {
        $plugin->setFilelib($this);
        
        foreach($plugin->getProfiles() as $profileIdentifier) {
            $profile = $this->file()->getProfile($profileIdentifier);
            $profile->addPlugin($plugin, $priority);
        }
        $plugin->init();
        return $this;
    }
    
    
}