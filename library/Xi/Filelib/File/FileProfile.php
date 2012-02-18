<?php

namespace Xi\Filelib\File;

use Xi\Filelib\Plugin\PriorityQueue;
use Xi\Filelib\FilelibException;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Configurator;
use Xi\Filelib\Linker\Linker;
use Xi\Filelib\Plugin\Plugin;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\File\File;

/**
 * File profile
 * 
 * @author pekkis
 *
 */
class FileProfile
{

    /**
     * @var \Xi\Filelib\FileLibrary
     */
    private $filelib;

    /**
     * @var \Xi_Filelib_Linker Linker
     */
    private $linker;

    /**
     * @var array Versions for file types
     */
    private $fileVersions = array();

    /**
     * @var string Human readable identifier
     */
    private $description;

    /**
     * @var string Machine readable identifier
     */
    private $identifier;

    /**
     * @var array Array of plugins
     */
    private $plugins = array();

    /**
     * @var boolean Allow access to original file
     */
    private $accessToOriginal = true;

    /**
     * @var boolean Publish original file
     */
    private $publishOriginal = true;

    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }

    /**
     * Sets filelib
     *
     * @param FileLibrary $filelib
     * 
     */
    public function setFilelib(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
        return $this;
    }

    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->filelib;
    }

    /**
     * Returns linker
     *
     * @return Linker
     */
    public function getLinker()
    {
        return $this->linker;
    }

    /**
     * Sets linker
     *
     * @param Linker
     * @return FileLibrary
     */
    public function setLinker($linker)
    {
        $this->linker = $linker;
        $linker->init();
        return $this;
    }

    /**
     * Sets human readable identifier
     * 
     * @param string $description
     * @return FileProfile
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Returns human readable identifier
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * Sets identifier
     * 
     * @param string $identifier
     * @throws \InvalidArgumentException
     * @return FileProfile
     */
    public function setIdentifier($identifier)
    {
        if ($identifier === 'original') {
            throw new \InvalidArgumentException("Invalid profile identifier '{$identifier}'");
        }
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Adds a plugin
     *
     * @param Plugin Plugin $plugin
     * @return FileProfile
     */
    public function addPlugin(Plugin $plugin, $priority = 1000)
    {
        $this->plugins[] = $plugin;
        return $this;
    }

    /**
     * Returns all plugins
     *
     * @return array Array of plugins
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * Adds a file version
     *
     * @param string $fileType string File type
     * @param string $versionIdentifier Version identifier
     * @param VersionProvider $versionProvider Version provider
     * @return \Xi\Filelib\File\FileProfile
     */
    public function addFileVersion($fileType, $versionIdentifier, VersionProvider $versionProvider)
    {
        $this->ensureFileVersionArrayExists($fileType);
        $this->fileVersions[$fileType][$versionIdentifier] = $versionProvider;

        return $this;
    }

    /**
     * Returns all defined versions of a file
     *
     * @param \Xi\Filelib\File\File $fileType File item
     * @return array Array of provided versions
     */
    public function getFileVersions(\Xi\Filelib\File\File $file)
    {
        $fileType = $this->getFilelib()->file()->getType($file);
        $this->ensureFileVersionArrayExists($fileType);
        return array_keys($this->fileVersions[$fileType]);
    }

    /**
     * Returns whether a file has a certain version
     *
     * @param \Xi\Filelib\File\File $file File item
     * @param string $version Version
     * @return boolean
     */
    public function fileHasVersion(\Xi\Filelib\File\File $file, $version)
    {
        return (in_array($version, $this->getFileVersions($file)));
    }

    /**
     * Returns version provider for a file/version
     *
     * @param File $file File item
     * @param string $version Version
     * @return VersionProvider Provider
     */
    public function getVersionProvider(File $file, $version)
    {
        if (!$this->fileHasVersion($file, $version)) {
            throw new \InvalidArgumentException("File has no version '{$version}'");
        }
        
        $filetype = $this->getFilelib()->file()->getType($file);
        return $this->fileVersions[$filetype][$version];
    }

    /**
     * Sets whether access to the original file is allowed
     * 
     * @param boolean $accessToOriginal
     * @return FileLibrary
     */
    public function setAccessToOriginal($accessToOriginal)
    {
        $this->accessToOriginal = $accessToOriginal;
        return $this;
    }

    /**
     * Returns whether access to the original file is allowed
     * 
     * @return boolean
     */
    public function getAccessToOriginal()
    {
        return $this->accessToOriginal;
    }

    /**
     * Sets whether the original file is published
     * 
     * @param boolean $publishOriginal
     * @return FileLibrary
     */
    public function setPublishOriginal($publishOriginal)
    {
        $this->publishOriginal = $publishOriginal;
        return $this;
    }

    /**
     * Returns whether the original file is published
     * 
     * @return boolean
     */
    public function getPublishOriginal()
    {
        return $this->publishOriginal;
    }

    private function ensureFileVersionArrayExists($fileType)
    {
        if (!isset($this->fileVersions[$fileType])) {
            $this->fileVersions[$fileType] = array();
        }
    }

}