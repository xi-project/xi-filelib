<?php

namespace Xi\Filelib;

use \Xi\Filelib\Options, \Xi\Filelib\Cache;

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
     * @var array Array of installed plugins
     */
    private $_plugins = array();


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

    public function __construct(Configuration $config)
    {
        $this->_folderOperator = new Folder\FolderOperator($this);
        $this->_fileOperator = new File\FileOperator($this);
                
        $this->setTempDir($config->getTempDir());
        $this->setAcl($config->getAcl());
        $this->setBackend($config->getBackend());
        $this->setCache($config->getCache());
        $this->setStorage($config->getStorage());
        $this->setPublisher($config->getPublisher());
        
        $this->folder()->setClass($config->getFolderItemClass());
        $this->file()->setClass($config->getFileItemClass());
                
        foreach ($config->getProfiles() as $profile)
        {
            $this->file()->addProfile($profile);
        }
        
        foreach ($config->getPlugins() as $plugin)
        {
            $this->addPlugin($plugin);
        }
        
    }
    
    
    /**
     * Sets temporary directory
     * 
     * @param string $tempDir
     */
    protected function setTempDir($tempDir)
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
        return $this->_tempDir ?: sys_get_temp_dir();
    }

    /**
     * Sets cache
     * 
     * @param \Xi\Filelib\Cache\Cache $cache
     * @return \Xi\Filelib\FileLibrary
     */
    protected function setCache(Cache\Cache $cache)
    {
        $this->_cache = $cache;
        return $this;
    }

    /**
     * Returns cache. If cache does not exist, init a mock cache
     * 
     * @return \Zend_Cache_Core
     */
    public function getCache()
    {
        if(!$this->_cache) {
            $this->_cache = new Cache\MockCache();
        }
        return $this->_cache;
    }

    /**
     * Returns file operator
     * 
     * @return \Xi\Filelib\File\FileOperator
     */
    public function file()
    {
        return $this->_fileOperator;
    }

    /**
     * Returns folder operator
     * 
     * @return \Xi\Filelib\Folder\FolderOperator
     */
    public function folder()
    {
        return $this->_folderOperator;
    }
    
    /**
     * Sets storage
     *
     * @param \Xi\Filelib\Storage\Storage $storage
     * @return \Xi\Filelib\FileLibrary
     */
    protected function setStorage(Storage\Storage $storage)
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
        if(!$this->_storage) {
            throw new FilelibException('Filelib storage not set');
        }

        return $this->_storage;
    }
    
    /**
     * Sets publisher
     *
     * @param \Xi\Filelib\Publisher\Interface $publisher
     * @return \Xi\Filelib\FileLibrary
     */
    protected function setPublisher(Publisher\Publisher $publisher)
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
        if(!$this->_publisher) {
            throw new FilelibException('Filelib Publisher not set');
        }

        return $this->_publisher;
    }

    /**
     * Sets backend
     *
     * @param \Xi\Filelib\Backend\Backend $backend
     * @return \Xi\Filelib\FileLibrary
     */
    protected function setBackend(Backend\Backend $backend)
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
        if(!$this->_backend) {
            throw new FilelibException('Filelib backend not set');
        }

        return $this->_backend;
    }

    /**
     * Adds a plugin
     *
     * @param \Xi\Filelib\Plugin\Plugin Plugin $plugin
     * @return \Xi\Filelib\FileLibrary
     */
    protected function addPlugin(Plugin\Plugin $plugin, $priority = 1000)
    {
        $plugin->setFilelib($this);
        foreach($plugin->getProfiles() as $profileIdentifier) {
            $profile = $this->file()->getProfile($profileIdentifier);
            $profile->addPlugin($plugin, $priority);
        }
        $plugin->init();
        return $this;
    }

    /**
     * Sets acl handler
     *
     * @param \Xi\Filelib\Acl\Acl $acl
     * @return \Xi\Filelib\FileLibrary Filelib
     */
    protected function setAcl(Acl\Acl $acl)
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
        if(!$this->_acl) {
            $this->_acl = new Acl\SimpleAcl();
        }
        return $this->_acl;
    }
    
}