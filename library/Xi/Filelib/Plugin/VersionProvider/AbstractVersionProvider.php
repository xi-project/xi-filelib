<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\VersionProvider;

use Xi\Filelib\File\File;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Event\ResourceEvent;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Events;
use Xi\Filelib\File\MimeType;
use Xi\Filelib\File\FileObject;

/**
 * Abstract convenience class for version provider plugins
 *
 * @author pekkis
 */
abstract class AbstractVersionProvider extends AbstractPlugin implements VersionProvider
{
    protected static $subscribedEvents = array(
        Events::PROFILE_AFTER_ADD => 'onFileProfileAdd',
        Events::FILE_AFTER_AFTERUPLOAD => 'onAfterUpload',
        Events::FILE_AFTER_DELETE => 'onFileDelete',
        Events::RESOURCE_AFTER_DELETE => 'onResourceDelete',
    );

    /**
     * @var string Version identifier
     */
    protected $identifier;

    /**
     * @var array Array of file types for which the plugin provides a version
     */
    protected $providesFor;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var FileOperator
     */
    protected $fileOperator;

    /**
     * @param string $identifier
     * @param callable $providesFor
     */
    public function __construct($identifier, $providesFor)
    {
        $this->identifier = $identifier;
        $this->providesFor = $providesFor;
    }

    /**
     * @param FileLibrary $filelib
     */
    public function setDependencies(FileLibrary $filelib)
    {
        $this->storage = $filelib->getStorage();
        $this->fileOperator = $filelib->getFileOperator();
        $this->init();
    }

    abstract public function createVersions(File $file);

    /**
     * Registers a version to all profiles
     */
    public function init()
    {
        foreach ($this->getProfiles() as $profile) {
            $profile = $this->fileOperator->getProfile($profile);
            foreach ($this->getVersions() as $version) {
                $profile->addFileVersion($version, $this);
            }
        }
    }

    /**
     * Returns identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns file types which the version plugin provides version for.
     *
     * @return array
     */
    public function getProvidesFor()
    {
        return $this->providesFor;
    }

    /**
     * Returns whether the plugin provides a version for a file.
     *
     * @param  File    $file File item
     * @return boolean
     */
    public function providesFor(File $file)
    {
        if (!in_array($file->getProfile(), $this->getProfiles())) {
            return false;
        }
        return call_user_func($this->providesFor, $file);
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

    public function onAfterUpload(FileEvent $event)
    {

        $file = $event->getFile();

        if (!$this->hasProfile($file->getProfile()) || !$this->providesFor($file) || $this->areVersionsCreated($file)) {
            return;
        }

        $tmps = $this->createVersions($file);

        $versionable = $this->areSharedVersionsAllowed() ? $file->getResource() : $file;

        foreach (array_keys($tmps) as $version) {
            $versionable->addVersion($version);
        }

        foreach ($tmps as $version => $tmp) {
            $this->getStorage()->storeVersion($file->getResource(), $version, $tmp, $this->areSharedVersionsAllowed() ? null : $file);
            unlink($tmp);
        }
    }

    public function onFileDelete(FileEvent $event)
    {
        $file = $event->getFile();

        if (!$this->hasProfile($file->getProfile())) {
            return;
        }

        if (!$this->providesFor($file)) {
            return;
        }
        $this->deleteFileVersions($file);
    }

    /**
     * Deletes resource versions
     *
     * @param ResourceEvent $event
     */
    public function onResourceDelete(ResourceEvent $event)
    {
        foreach ($this->getVersions() as $version) {
            if ($this->getStorage()->versionExists($event->getResource(), $version)) {
                $this->getStorage()->deleteVersion($event->getResource(), $version);
            }
        }
    }

    /**
     * Deletes file versions
     *
     * @param File $file
     */
    public function deleteFileVersions(File $file)
    {
        foreach ($this->getVersions() as $version) {
            if ($this->getStorage()->versionExists($file->getResource(), $version, $file)) {
                $this->getStorage()->deleteVersion($file->getResource(), $version, $file);
            }
        }
    }

    public function areVersionsCreated(File $file)
    {
        $versionable = $this->areSharedVersionsAllowed() ? $file->getResource() : $file;

        $count = 0;
        foreach ($this->getVersions() as $version) {
            if ($versionable->hasVersion($version)) {
                $count++;
            }
        }
        if ($count == count($this->getVersions())) {
            return true;
        }

        return false;
    }

    public function getExtensionFor(File $file, $version)
    {
        $retrieved = $this->storage->retrieveVersion(
            $file->getResource(),
            $version->getIdentifier(),
            $this->areSharedVersionsAllowed() ? null : $file
        );

        $fileObj = new FileObject($retrieved);
        $extensions = MimeType::mimeTypeToExtensions($fileObj->getMimeType());
        if (!count($extensions)) {
            throw new \RuntimeException("Failed to find an extension for mime type '{$fileObj->getMimeType()}'");
        }

        return array_shift($extensions);
    }

}
