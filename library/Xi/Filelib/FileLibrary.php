<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Pekkis\Queue\SymfonyBridge\EventDispatchingQueue;
use Xi\Filelib\Cache\Cache;
use Xi\Filelib\Command\Commander;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Plugin\Plugin;
use Xi\Filelib\Profile\FileProfile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Xi\Filelib\Event\PluginEvent;
use Xi\Filelib\Backend\Platform\Platform;
use Pekkis\Queue\Adapter\Adapter as QueueAdapter;
use Pekkis\Queue\Queue;
use Xi\Filelib\Command\CommandDataSerializer;
use Xi\Filelib\Profile\ProfileManager;

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
     * @var Storage
     */
    private $storage;

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
     * @var Platform
     */
    private $platform;

    /**
     * @var array
     */
    private $plugins = array();

    /**
     * @var Commander
     */
    private $commander;

    /**
     * @var ProfileManager
     */
    private $profileManager;

    public function __construct(
        Storage $storage,
        Platform $platform,
        EventDispatcherInterface $eventDispatcher = null,
        Commander $commander = null
    ) {
        if (!$eventDispatcher) {
            $eventDispatcher = new EventDispatcher();
        }

        if (!$commander) {
            $commander = new Commander($this);
        }

        $this->storage = $storage;
        $this->platform = $platform;
        $this->eventDispatcher = $eventDispatcher;
        $this->profileManager = new ProfileManager($this->eventDispatcher);
        $this->commander = $commander;

        $this->backend = new Backend(
            $this->getEventDispatcher(),
            $this->platform
        );

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
     * Returns file operator
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
     * Returns folder operator
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
     * Adds a plugin
     *
     * @param  Plugin      $plugin
     * @return FileLibrary
     *
     */
    public function addPlugin(Plugin $plugin, $profiles = array())
    {
        $this->plugins[] = $plugin;

        if (!$profiles) {
            $resolverFunc = function ($profile) {
                return true;
            };
        } else {
            $resolverFunc = function ($profile) use ($profiles) {
                return (bool) in_array($profile, $profiles);
            };
        }

        $plugin->setHasProfileResolver($resolverFunc);
        $plugin->attachTo($this);

        $this->getEventDispatcher()->addSubscriber($plugin);
        $event = new PluginEvent($plugin);
        $this->getEventDispatcher()->dispatch(Events::PLUGIN_AFTER_ADD, $event);
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
     * @return Platform
     */
    public function getPlatform()
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
    public function setCache(Cache $cache)
    {
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
