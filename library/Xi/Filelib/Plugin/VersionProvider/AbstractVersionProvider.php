<?php

namespace Xi\Filelib\Plugin\VersionProvider;

use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;
use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Event\FileEvent;

/**
 * Abstract convenience class for version provider plugins
 *
 * @package Xi_Filelib
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

    /**
     * @var File extension for the version
     */
    protected $extension;

    abstract public function createVersion(File $file);

    /**
     * Registers a version to all profiles
     */
    public function init()
    {
        if (!$this->getIdentifier()) {
            throw new FilelibException('Version plugin must have an identifier');
        }

        if (!$this->getExtension()) {
            throw new FilelibException('Version plugin must have a file extension');
        }

        foreach ($this->getProvidesFor() as $fileType) {
            foreach ($this->getProfiles() as $profile) {
                $profile = $this->getFilelib()->getFileOperator()->getProfile($profile);
                $profile->addFileVersion($fileType, $this->getIdentifier(), $this);
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
     * Sets file extension
     *
     * @param string $extension File extension
     * @return VersionProvider
     */
    public function setExtension($extension)
    {
        $extension = str_replace('.', '', $extension);
        $this->extension = $extension;
        return $this;
    }

    /**
     * Returns the plugins file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    public function afterUpload(FileEvent $event)
    {
        $file = $event->getFile();

        if (!$this->hasProfile($file->getProfile())) {
            return;
        }
        
        if (!$this->providesFor($file)) {
            return;
        }

        $tmp = $this->createVersion($file);
        $this->getFilelib()->getStorage()->storeVersion($file, $this->getIdentifier(), $tmp);
        unlink($tmp);
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
        
        $this->getFilelib()->getPublisher()->publishVersion($file, $this);
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

        $this->getFilelib()->getPublisher()->unpublishVersion($file, $this);
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

        $this->deleteVersion($file);
    }

    /**
     * Deletes a version
     * 
     * @param $file File
     * 
     */
    public function deleteVersion(File $file)
    {
        $this->getFilelib()->getStorage()->deleteVersion($file, $this->getIdentifier());
    }

}
