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
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Acl\Acl;
use Xi\Filelib\File\FileProfile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use InvalidArgumentException;
use Xi\Filelib\Event\PluginEvent;
use Xi\Filelib\Queue\Queue;
use Xi\Filelib\IdentityMap\IdentityMap;
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
     * @var Acl
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
     * Sets backend
     *
     * @param  Backend     $backend
     * @return FileLibrary
     */
    public function setBackend(Backend $backend)
    {
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
     * @param  Acl         $acl
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
        if (!$this->acl) {
            $this->acl = new \Xi\Filelib\Acl\SimpleAcl(true);
        }

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
     * @param  Plugin      $plugin
     * @return FileLibrary
     *
     */
    public function addPlugin(Plugin $plugin, $profiles = array())
    {
        // @todo: think about dependency hell
        $plugin->setProfiles($profiles);
        $plugin->setDependencies($this);

        $this->getEventDispatcher()->addSubscriber($plugin);
        $event = new PluginEvent($plugin);
        $this->getEventDispatcher()->dispatch('xi_filelib.plugin.add', $event);
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

    /**
     * Prototyping a general shortcut magic method. Is it bad?
     *
     * @param $method
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        $matches = array();
        if (preg_match("#^(.*?)(Folder|File)$#", $method, $matches)) {
            $delegate = ($matches[2] == 'Folder') ? $this->getFolderOperator() : $this->getFileOperator();
            return call_user_func_array(array($delegate, $matches[1]), $args);
        }
        throw new \Exception("Invalid method '{$method}'");
    }


}
