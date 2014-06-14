<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Pekkis\Queue\SymfonyBridge\EventDispatchingQueue;
use Xi\Filelib\Backend\Cache\Adapter\CacheAdapter;
use Xi\Filelib\Backend\Cache\Cache;
use Xi\Filelib\Command\Commander;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Plugin\PluginManager;
use Xi\Filelib\Resource\ResourceRepository;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Plugin\Plugin;
use Xi\Filelib\Profile\FileProfile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Xi\Filelib\Event\PluginEvent;
use Xi\Filelib\Backend\Adapter\BackendAdapter;
use Pekkis\Queue\Adapter\Adapter as QueueAdapter;
use Pekkis\Queue\Queue;
use Xi\Filelib\Command\CommandDataSerializer;
use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\Storage\Adapter\StorageAdapter;
use Xi\Filelib\Storage\Storage;

/**
 * File library
 *
 * @author pekkis
 * @todo Refactor to configuration / contain common methods (getFile etc)
 *
 */
class FileLibrary
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Backend
     */
    private $backend;

    /**
     * @var StorageAdapter
     */
    private $storage;

    /**
     * @var ResourceRepository
     */
    private $resourceRepository;

    /**
     * @var FileRepository
     */
    private $fileRepository;

    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var EventDispatchingQueue
     */
    private $queue;

    /**
     * @var BackendAdapter
     */
    private $platform;

    /**
     * @var Commander
     */
    private $commander;

    /**
     * @var ProfileManager
     */
    private $profileManager;

    /**
     * @var PluginManager
     */
    private $pluginManager;

    public function __construct(
        StorageAdapter $storageAdapter,
        BackendAdapter $platform,
        EventDispatcherInterface $eventDispatcher = null,
        Commander $commander = null
    ) {
        if (!$eventDispatcher) {
            $eventDispatcher = new EventDispatcher();
        }

        if (!$commander) {
            $commander = new Commander($this);
        }

        $this->platform = $platform;
        $this->eventDispatcher = $eventDispatcher;
        $this->profileManager = new ProfileManager($this->eventDispatcher);
        $this->pluginManager = new PluginManager($this->eventDispatcher);
        $this->commander = $commander;

        $this->backend = new Backend(
            $this->getEventDispatcher(),
            $this->platform
        );

        $this->storage = new Storage(
            $storageAdapter
        );
        $this->storage->attachTo($this);

        $this->addProfile(new FileProfile('default'));
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @return ProfileManager
     */
    public function getProfileManager()
    {
        return $this->profileManager;
    }

    /**
     * @return PluginManager
     */
    public function getPluginManager()
    {
        return $this->pluginManager;
    }

    /**
     * Sets temporary directory
     *
     * @param string $tempDir
     */
    public function setTempDir($tempDir)
    {
        if (!is_dir($tempDir) || !is_writable($tempDir)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Temp dir "%s" is not writable or does not exist',
                    $tempDir
                )
            );
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
     * Returns resource repository
     *
     * @return ResourceRepository
     */
    public function getResourceRepository()
    {
        if (!$this->resourceRepository) {
            $this->resourceRepository = new ResourceRepository();
            $this->resourceRepository->attachTo($this);
        }
        return $this->resourceRepository;
    }


    /**
     * Returns file repository
     *
     * @return FileRepository
     */
    public function getFileRepository()
    {
        if (!$this->fileRepository) {
            $this->fileRepository = new FileRepository();
            $this->fileRepository->attachTo($this);
        }
        return $this->fileRepository;
    }

    /**
     * Returns folder repository
     *
     * @return FolderRepository
     */
    public function getFolderRepository()
    {
        if (!$this->folderRepository) {
            $this->folderRepository = new FolderRepository();
            $this->folderRepository->attachTo($this);
        }

        return $this->folderRepository;
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
     * Returns backend
     *
     * @return Backend
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * Adds a file profile
     *
     * @param FileProfile $profile
     */
    public function addProfile(FileProfile $profile)
    {
        $this->getProfileManager()->addProfile($profile);
    }

    /**
     * Returns all file profiles
     *
     * @return array
     */
    public function getProfiles()
    {
        return $this->getProfileManager()->getProfiles();
    }

    /**
     * @param $identifier
     * @return FileProfile
     */
    public function getProfile($identifier)
    {
        return $this->getProfileManager()->getProfile($identifier);
    }

    /**
     * @param Plugin $plugin
     * @param array $profiles Profiles to add to, empty array to add to all profiles
     * @param string $name
     * @return FileLibrary
     */
    public function addPlugin(Plugin $plugin, $profiles = array(), $name = null)
    {
        $plugin->attachTo($this);
        $this->pluginManager->addPlugin($plugin, $profiles, $name);
        return $this;
    }

    /**
     * Sets queue
     *
     * @param QueueAdapter $adapter
     */
    public function createQueueFromAdapter(QueueAdapter $adapter)
    {
        $queue = new Queue($adapter);
        $queue->addDataSerializer(
            new CommandDataSerializer($this)
        );

        $this->queue = new EventDispatchingQueue(
            $queue,
            $this->getEventDispatcher()
        );

        $this->commander->setQueue($this->queue);
        return $this;
    }

    /**
     * Returns queue
     *
     * @return EventDispatchingQueue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * Returns platform
     *
     * @return BackendAdapter
     */
    public function getBackendAdapter()
    {
        return $this->platform;
    }

    /**
     * @return Commander
     */
    public function getCommander()
    {
        return $this->commander;
    }

    /**
     * @param string|FileUpload $file
     * @param Folder $folder
     * @param string $profile
     * @return File
     */
    public function upload($file, $folder = null, $profile = 'default')
    {
        return $this->getFileRepository()->upload($file, $folder, $profile);
    }

    /**
     * @param Cache $cache
     * @return FileLibrary
     */
    public function createCacheFromAdapter(CacheAdapter $adapter)
    {
        $cache = new Cache($adapter);
        $this->getBackend()->setCache($cache);
        return $this;
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->getBackend()->getCache();
    }
}
