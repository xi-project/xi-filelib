<?php

namespace Xi\Filelib;

use Xi\Filelib\Folder\FolderOperator,
    Xi\Filelib\File\FileOperator;

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
     * Temporary directory
     * 
     * @var string
     */
    private $_tempDir;

    /**
     * Sets temporary directory
     * 
     * @param string $tempDir
     */
    public function setTempDir($tempDir)
    {
        if (!is_dir($tempDir) || !is_writable($tempDir)) {
            throw new \InvalidArgumentException("Temp dir is not writable or does not exist");
        }
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
     * @return FileLibrary
     */
    public function setFileOperator(FileOperator $fileOperator)
    {
        $this->_fileOperator = $fileOperator;
        return $this;
    }

    /**
     * Sets folder operator
     * 
     * @param \Xi\Filelib\Folder\FolderOperator $fileOperator 
     * @return FileLibrary
     */
    public function setFolderOperator(FolderOperator $folderOperator)
    {
        $this->_folderOperator = $folderOperator;
        return $this;
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
        $this->getFileOperator()->setClass($fileItemClass);
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
        $this->getFolderOperator()->setClass($folderItemClass);
        return $this;
    }

    /**
     * Returns fully qualified folderitem classname
     * 
     * @return string
     */
    public function getFolderItemClass()
    {
        return $this->getFolderOperator()->getClass();
    }

    /**
     * Returns fully qualified fileitem classname
     * 
     * @return string
     */
    public function getFileItemClass()
    {
        return $this->getFileOperator()->getClass();
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
        $this->getFileOperator()->addProfile($profile);
    }

    /**
     * Returns all file profiles
     * 
     * @return array
     */
    public function getProfiles()
    {
        return $this->getFileOperator()->getProfiles();
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
        $this->getFileOperator()->addPlugin($plugin, $priority);
        $plugin->init();
        return $this;
    }

}