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
use Xi\Filelib\Backend\Backend;
use Xi\Filelib\Plugin\Plugin;
use Xi\Filelib\File\FileProfile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Xi\Filelib\Event\PluginEvent;
use Xi\Filelib\Queue\Queue;
use Xi\Filelib\Backend\Platform\Platform;

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
     * @var FileOperator
     */
    private $fileOperator;

    /**
     * @var FolderOperator
     */
    private $folderOperator;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var Platform
     */
    private $platform;

    private $plugins;


    public function __construct(
        Storage $storage,
        Platform $platform,
        EventDispatcherInterface $eventDispatcher = null
    ) {

        $this->storage = $storage;
        $this->platform = $platform;
        $this->eventDispatcher = $eventDispatcher;

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
        if (!$this->eventDispatcher) {
            $this->eventDispatcher = new EventDispatcher();
        }
        return $this->eventDispatcher;
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
     * @param $identifier
     * @return FileProfile
     */
    public function getProfile($identifier)
    {
        return $this->getFileOperator()->getProfile($identifier);
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
     * Returns platform
     *
     * @return Platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    public function upload($file, $folder = null, $profile = 'default')
    {
        return $this->getFileOperator()->upload($file, $folder, $profile);
    }
}
