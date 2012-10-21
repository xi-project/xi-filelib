<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Backend\Platform\Platform;
use Xi\Filelib\Plugin\Plugin;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Acl\Acl;
use Xi\Filelib\File\FileProfile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use InvalidArgumentException;
use Xi\Filelib\Event\PluginEvent;
use Xi\Filelib\Event\FilelibEvent;
use Xi\Filelib\Queue\Queue;

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
     * @var Platform Backend
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
     *
     * @var Queue
     */
    private $queue;



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
     * @deprecated
     */
    public function file()
    {
        trigger_error( "Method is deprecated. use getFileOperator() instead.", E_USER_DEPRECATED);
        return $this->getFileOperator();
    }

    /**
     * Shortcut to getFolderOperator
     *
     * @deprecated
     * @return FolderOperator
     */
    public function folder()
    {
        trigger_error( "Method is deprecated. use getFolderOperator() instead.", E_USER_DEPRECATED);
        return $this->getFolderOperator();
    }

    /**
     * Sets file operator
     *
     * @param FileOperator $fileOperator
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
     * @param FolderOperator $fileOperator
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
            $this->fileOperator = new FileOperator($this);
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
            $this->folderOperator = new FolderOperator($this);
        }

        return $this->folderOperator;
    }

    /**
     * Sets storage
     *
     * @param Storage $storage
     * @return FileLibrary
     */
    public function setStorage(Storage $storage)
    {
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
     * @param Platform $backend
     * @return FileLibrary
     */
    public function setBackend(Platform $backend)
    {
        $this->backend = $backend;
        return $this;
    }

    /**
     * Returns backend
     *
     * @return Platform
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

        $this->getEventDispatcher()->addSubscriber($plugin);

        $event = new PluginEvent($plugin);
        $this->getEventDispatcher()->dispatch('plugin.add', $event);

        $plugin->init();
        return $this;
    }

    /**
     * Sets queue
     *
     * @param Queue $queue
     */
    public function setQueue(Queue $queue)
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * Returns queue
     *
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }


    /**
     * Triggers init event. Kind of a kludge until better rethought.
     *
     */
    public function dispatchInitEvent()
    {
        $event = new FilelibEvent($this);
        $this->getEventDispatcher()->dispatch('filelib.init', $event);
    }


}
