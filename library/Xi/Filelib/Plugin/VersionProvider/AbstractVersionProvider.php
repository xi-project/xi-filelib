<?php

namespace Xi\Filelib\Plugin\VersionProvider;

use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Publisher\Publisher;

/**
 * Abstract convenience class for version provider plugins
 *
 * @author pekkis
 *
 */
abstract class AbstractVersionProvider extends AbstractPlugin implements VersionProvider
{

    static protected $subscribedEvents = array(
        'fileprofile.add' => 'onFileProfileAdd',
        'file.afterUpload' => 'afterUpload',
        'file.publish' => 'onPublish',
        'file.unpublish' => 'onUnpublish',
        'file.delete' => 'onDelete',
    );

    /**
     * @var string Version identifier
     */
    protected $identifier;

    /**
     * @var array Array of file types for which the plugin provides a version
     */
    protected $providesFor = array();

    abstract public function createVersions(File $file);

    /**
     * Registers a version to all profiles
     */
    public function init()
    {
        if (!$this->getIdentifier()) {
            throw new FilelibException('Version plugin must have an identifier');
        }

        foreach ($this->getProvidesFor() as $fileType) {
            foreach ($this->getProfiles() as $profile) {
                $profile = $this->getFilelib()->getFileOperator()->getProfile($profile);

                foreach ($this->getVersions() as $version) {
                    $profile->addFileVersion($fileType, $version, $this);
                }

            }
        }
    }

    /**
     * Sets identifier
     *
     * @param string $identifier
     * @return VersionProvider
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
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
     * Sets file types for this version plugin.
     *
     * @param array $providesFor Array of file types
     * @return VersionProvider
     */
    public function setProvidesFor(array $providesFor)
    {
        $this->providesFor = $providesFor;
        return $this;
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
     * @param File $file File item
     * @return boolean
     */
    public function providesFor(File $file)
    {
        if (in_array($this->getFilelib()->getFileOperator()->getType($file), $this->getProvidesFor())) {
            if (in_array($file->getProfile(), $this->getProfiles())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns storage
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->getFilelib()->getStorage();
    }

    /**
     * Returns publisher
     *
     * @return Publisher
     */
    public function getPublisher()
    {
        return $this->getFilelib()->getPublisher();
    }

    public function afterUpload(FileEvent $event)
    {
        $file = $event->getFile();

        if (!$this->hasProfile($file->getProfile()) || !$this->providesFor($file) || $this->areVersionsCreated($file)) {
            return;
        }

        $tmps = $this->createVersions($file);

        foreach (array_keys($tmps) as $version) {
            $file->getResource()->addVersion($version);
        }

        foreach ($tmps as $version => $tmp) {
            $this->getStorage()->storeVersion($file->getResource(), $version, $tmp, $this->areSharedVersionsAllowed() ? null : $file);
            unlink($tmp);
        }
    }

    public function onPublish(FileEvent $event)
    {
        $file = $event->getFile();

        if (!$this->hasProfile($file->getProfile())) {
            return;
        }

        if (!$this->providesFor($file)) {
            return;
        }

        foreach ($this->getVersions() as $version) {
            $this->getPublisher()->publishVersion($file, $version, $this);
        }

    }

    public function onUnpublish(FileEvent $event)
    {
        $file = $event->getFile();

        if (!$this->hasProfile($file->getProfile())) {
            return;
        }

        if (!$this->providesFor($file)) {
            return;
        }

        foreach ($this->getVersions() as $version) {
            $this->getPublisher()->unpublishVersion($file, $version, $this);
        }
    }

    public function onDelete(FileEvent $event)
    {
        $file = $event->getFile();

        if (!$this->hasProfile($file->getProfile())) {
            return;
        }

        if (!$this->providesFor($file)) {
            return;
        }

        $this->deleteVersions($file);
    }

    /**
     * Deletes a version
     *
     * @param $file File
     *
     */
    public function deleteVersions(File $file)
    {
        foreach ($this->getVersions() as $version) {
            $this->getStorage()->deleteVersion($file->getResource(), $version, $this->areSharedVersionsAllowed() ? null : $file);
        }
    }


    public function areVersionsCreated(File $file)
    {
        // @todo solve this somehow
        if (!$this->areSharedVersionsAllowed()) {
            return false;
        }

        $count = 0;
        foreach ($this->getVersions() as $version) {
            if ($file->getResource()->hasVersion($version)) {
                $count++;
            }
        }
        if ($count == count($this->getVersions())) {
            return true;
        }
        return false;
    }


}
