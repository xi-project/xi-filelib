<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Pekkis\Queue\SymfonyBridge\EventDispatchingQueue;
use Pekkis\TemporaryFileManager\TemporaryFileManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Collections\Collection\ArrayCollection;
use Xi\Filelib\Backend\Adapter\BackendAdapter;
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Backend\Cache\Adapter\CacheAdapter;
use Xi\Filelib\Backend\Cache\Cache;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\File\FileRepositoryInterface;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\Folder\FolderRepositoryInterface;
use Xi\Filelib\Plugin\Plugin;
use Xi\Filelib\Plugin\PluginManager;
use Xi\Filelib\Profile\FileProfile;
use Xi\Filelib\Profile\ProfileManager;
use Xi\Filelib\Resource\ResourceRepository;
use Xi\Filelib\Resource\ResourceRepositoryInterface;
use Xi\Filelib\Storage\Adapter\StorageAdapter;
use Xi\Filelib\Storage\Storage;

class FileLibrary
{
    const VERSION = '0.14.0-dev';

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
     * @var ResourceRepositoryInterface
     */
    private $resourceRepository;

    /**
     * @var FileRepositoryInterface
     */
    private $fileRepository;

    /**
     * @var FolderRepositoryInterface
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
    private $backendAdapter;

    /**
     * @var ProfileManager
     */
    private $profileManager;

    /**
     * @var PluginManager
     */
    private $pluginManager;

    /**
     * @var TemporaryFileManager
     */
    private $temporaryFileManager;

    public function __construct(
        $storageAdapter,
        $backendAdapter,
        EventDispatcherInterface $eventDispatcher = null,
        $tempDir = null
    ) {
        if (!$eventDispatcher) {
            $eventDispatcher = new EventDispatcher();
        }

        if (!$tempDir) {
            $tempDir = sys_get_temp_dir();
        }

        if (!$tempDir instanceof TemporaryFileManager) {
            $tempDir = new TemporaryFileManager($tempDir);
        }

        $this->temporaryFileManager = $tempDir;

        $this->backendAdapter = $backendAdapter;
        $this->eventDispatcher = $eventDispatcher;
        $this->profileManager = new ProfileManager($this->eventDispatcher);
        $this->pluginManager = new PluginManager($this);

        $this->backend = new Backend(
            $this->getEventDispatcher(),
            $this->backendAdapter
        );

        $this->storage = new Storage(
            $storageAdapter
        );
        $this->storage->attachTo($this);

        $this->addProfile(new FileProfile('default'));
    }

    /**
     * Uploads a file to filelib
     *
     * @param string|FileUpload $file
     * @param Folder $folder Folder or null for root folder
     * @param string $profile File profile name
     * @return File
     */
    public function uploadFile($file, $folder = null, $profile = 'default')
    {
        $file = $this->getFileRepository()->upload($file, $folder, $profile);
        $this->getFileRepository()->afterUpload($file);

        return $file;
    }

    /**
     * Returns a folder by url. Creates one if one does not exist.
     *
     * @param string $url
     * @return Folder
     */
    public function createFolderByUrl($url)
    {
        return $this->getFolderRepository()->createByUrl($url);
    }

    /**
     * @param mixed $id
     * @return File
     */
    public function findFile($id)
    {
        return $this->getFileRepository()->find($id);
    }

    /**
     * @param array $ids
     * @return ArrayCollection
     */
    public function findFiles(array $ids = array())
    {
        return $this->getFileRepository()->findMany($ids);
    }

    /**
     * @param FileFinder $finder
     * @return ArrayCollection
     */
    public function findFilesBy(FileFinder $finder)
    {
        return $this->getFileRepository()->findBy($finder);
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
     * Returns temporary file manager
     *
     * @return string
     */
    public function getTemporaryFileManager()
    {
        return $this->temporaryFileManager;
    }

    /**
     * Returns resource repository
     *
     * @return ResourceRepositoryInterface
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
     * @param ResourceRepositoryInterface $resourceRepository
     * @return FileLibrary
     */
    public function setResourceRepository(ResourceRepositoryInterface $resourceRepository)
    {
        $this->resourceRepository = $resourceRepository;
        return $this;
    }

    /**
     * Returns file repository
     *
     * @return FileRepositoryInterface
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
     * @param FileRepositoryInterface $fileRepository
     * @return FileLibrary
     */
    public function setFileRepository(FileRepositoryInterface $fileRepository)
    {
        $this->fileRepository = $fileRepository;
        return $this;
    }

    /**
     * Returns folder repository
     *
     * @return FolderRepositoryInterface
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
     * @param FolderRepositoryInterface $folderRepository
     * @return FileLibrary
     */
    public function setFolderRepository(FolderRepositoryInterface $folderRepository)
    {
        $this->folderRepository = $folderRepository;
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
     * Returns backend adapter
     *
     * @return BackendAdapter
     */
    public function getBackendAdapter()
    {
        return $this->backendAdapter;
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
