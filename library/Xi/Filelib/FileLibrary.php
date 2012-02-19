<?php

namespace Xi\Filelib;

use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\DefaultFolderOperator;
use Xi\Filelib\File\DefaultFileOperator;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Plugin\Plugin;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Acl\Acl;
use Xi\Filelib\File\FileProfile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use InvalidArgumentException;

/**
 * Xi filelib
 *
 * @author pekkis
 *
 */
class FileLibrary
{

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    
    /**
     * @var Backend Backend
     */
    private $backend;

    /**
     * @var Storage Storage
     */
    private $storage;

    /**
     * @var Publisher Publisher
     */
    private $publisher;

    /**
     * @var Acl Acl handler
     */
    private $acl;

    /**
     * @var FileOperator
     */
    private $fileOperator;

    /**
     * @var FolderOperator
     */
    private $folderOperator;

    /**
     * Temporary directory
     * 
     * @var string
     */
    private $tempDir;

    
    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        if (!$this->eventDispatcher) {
            $this->eventDispatcher = new EventDispatcher();
        }
        return $this->eventDispatcher;
    }

    
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }
    
    
    
    /**
     * Sets temporary directory
     * 
     * @param string $tempDir
     */
    public function setTempDir($tempDir)
    {
        if (!is_dir($tempDir) || !is_writable($tempDir)) {
            throw new InvalidArgumentException("Temp dir is not writable or does not exist");
        }
        $this->tempDir = $tempDir;
    }

    /**
     * Returns temporary directory
     * 
     * @return string
     */
    public function getTempDir()
    {
        if (!$this->tempDir) {
            $this->setTempDir(sys_get_temp_dir());
        }

        return $this->tempDir;
    }

    /**
     * Shortcut to getFileOperator
     * 
     * @return FileOperator
     */
    public function file()
    {
        return $this->getFileOperator();
    }

    /**
     * Shortcut to getFolderOperator
     * 
     * @return FolderOperator
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
        $this->fileOperator = $fileOperator;
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
        $this->folderOperator = $folderOperator;
        return $this;
    }

    /**
     * Returns file operator
     * 
     * @return FileOperator
     */
    public function getFileOperator()
    {
        if (!$this->fileOperator) {
            $this->fileOperator = new DefaultFileOperator($this);
        }
        return $this->fileOperator;
    }

    /**
     * Returns folder operator
     * 
     * @return FolderOperator 
     */
    public function getFolderOperator()
    {
        if (!$this->folderOperator) {
            $this->folderOperator = new DefaultFolderOperator($this);
        }

        return $this->folderOperator;
    }

    /**
     * Sets fully qualified fileitem classname
     *
     * @param string $fileItemClass Class name
     * @return FileLibrary
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
     * @param Storage $storage
     * @return FileLibrary
     */
    public function setStorage(Storage $storage)
    {
        $storage->setFilelib($this);
        $this->storage = $storage;
        return $this;
    }

    /**
     * Returns storage
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Sets publisher
     *
     * @param Publisher $publisher
     * @return FileLibrary
     */
    public function setPublisher(Publisher $publisher)
    {
        $publisher->setFilelib($this);
        $this->publisher = $publisher;
        return $this;
    }

    /**
     * Returns publisher
     *
     * @return Publisher
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Sets backend
     *
     * @param Backend $backend
     * @return FileLibrary
     */
    public function setBackend(Backend $backend)
    {
        $backend->setFilelib($this);
        $backend->init();
        $this->backend = $backend;
        return $this;
    }

    /**
     * Returns backend
     *
     * @return Backend
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * Sets acl handler
     *
     * @param Acl $acl
     * @return FileLibrary Filelib
     */
    public function setAcl(Acl $acl)
    {
        $this->acl = $acl;
        return $this;
    }

    /**
     * Returns acl handler
     *
     * @return Acl
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * Adds a file profile
     * 
     * @param FileProfile $profile
     */
    public function addProfile(FileProfile $profile)
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
     * @param Plugin Plugin $plugin
     * @return FileLibrary
     */
    public function addPlugin(Plugin $plugin, $priority = 1000)
    {
        $plugin->setFilelib($this);
        $this->getFileOperator()->addPlugin($plugin, $priority);
        $plugin->init();
        return $this;
    }

}