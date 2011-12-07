<?php

namespace Xi\Filelib\File;

use \Xi\Filelib\Plugin\PriorityQueue;
use \Xi\Filelib\FilelibException;

/**
 * File profile
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
class FileProfile
{
    /**
     * @var \Xi\Filelib\FileLibrary
     */
    private $_filelib;

    /**
     * @var \Xi_Filelib_Linker Linker
     */
    private $_linker;

    /**
     * @var array Versions for file types
     */
    private $_fileVersions = array();
    
    /**
     * @var string Human readable identifier
     */
    private $_description;

    /**
     * @var string Machine readable identifier
     */
    private $_identifier;

    /**
     * @var boolean Selectable (in uis for example)
     */
    private $_selectable = true;

    /**
     * @var array Array of plugins
     */
    private $_plugins = array();


    /**
     * @var boolean Allow access to original file
     */
    private $_accessToOriginal = true;
    
    /**
     * @var boolean Publish original file
     */
    private $_publishOriginal = true;

    
    
    public function __construct($options = array())
    {
        \Xi\Filelib\Configurator::setConstructorOptions($this, $options);
    }
    
    
    /**
     * Sets filelib
     *
     * @param \Xi_Filelib $filelib
     */
    public function setFilelib(\Xi\Filelib\FileLibrary $filelib)
    {
        $this->_filelib = $filelib;
    }

    /**
     * Returns filelib
     *
     * @return \Xi_Filelib_Filelib
     */
    public function getFilelib()
    {
        return $this->_filelib;
    }

    /**
     * Returns linker
     *
     * @return \Xi\Filelib\Linker\Linker
     */
    public function getLinker()
    {
        if(!$this->_linker) {
            throw new \Xi\Filelib\FilelibException("File profile '{$this->getIdentifier()}' does not have a linker");
        }
        return $this->_linker;
    }


    /**
     * Sets linker
     *
     * @param \Xi\Filelib\Linker\Linker|string $linker
     * @return \Xi\Filelib\FileLibrary Filelib
     */
    public function setLinker($linker)
    {
        if(!$linker instanceof \Xi\Filelib\Linker\Linker) {
            $linker = new $linker($this);

        }
        $linker->init();
        $this->_linker = $linker;

        return $this;
    }

    /**
     * Sets human readable identifier
     * 
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }


    /**
     * Returns human readable identifier
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }


    /**
     * Returns identifier
     * 
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }


    /**
     * Sets identifier
     * 
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        if ($identifier === 'original') {
            throw new Xi\Filelib\FilelibException("Invalid profile identifier '{$identifier}'");
        }
        
        $this->_identifier = $identifier;
    }


    /**
     * Returns whether profile is selectable
     * 
     * @return boolean
     */
    public function getSelectable()
    {
        return $this->_selectable;
    }


    /**
     * Sets whether the profile is selectable
     * 
     * @param boolean $selectable
     */
    public function setSelectable($selectable)
    {
        $this->_selectable = $selectable;
    }


    /**
     * Adds a plugin
     *
     * @param \Xi\Filelib\Plugin\Plugin Plugin $plugin
     * @return \Xi\Filelib\File\FileProfile
     */
    public function addPlugin(\Xi\Filelib\Plugin\Plugin $plugin, $priority = 1000)
    {
        $this->_plugins[] = $plugin;
        return $this;
    }

    /**
     * Returns all plugins
     *
     * @return array Array of plugins
     */
    public function getPlugins()
    {
        return $this->_plugins;
    }


    /**
     * Adds a file version
     *
     * @param string $fileType string File type
     * @param string $versionIdentifier Version identifier
     * @param object $versionProvider Version provider reference
     * @return \Xi\Filelib\File\FileProfile
     */
    public function addFileVersion($fileType, $versionIdentifier, $versionProvider)
    {
        if(!isset($this->_fileVersions[$fileType])) {
            $this->_fileVersions[$fileType] = array();
        }
        $this->_fileVersions[$fileType][$versionIdentifier] = $versionProvider;

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

        if(!isset($this->_fileVersions[$fileType])) {
            $this->_fileVersions[$fileType] = array();
        }

        return array_keys($this->_fileVersions[$fileType]);

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
        $filetype = $this->getFilelib()->file()->getType($file);

        if(isset($this->_fileVersions[$filetype][$version])) {
            return true;
        }
        return false;
    }

    /**
     * Returns version provider for a file/version
     *
     * @param \Xi\Filelib\File\File $file File item
     * @param string $version Version
     * @return \Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider Provider
     */
    public function getVersionProvider(\Xi\Filelib\File\File $file, $version)
    {
        $filetype = $this->getFilelib()->file()->getType($file);
        return $this->_fileVersions[$filetype][$version];
    }

    
    /**
     * Sets whether access to the original file is allowed
     * 
     * @param boolean $accessToOriginal
     */
    public function setAccessToOriginal($accessToOriginal)
    {
        $this->_accessToOriginal = $accessToOriginal;
    }
        
    /**
     * Returns whether access to the original file is allowed
     * 
     * @return boolean
     */
    public function getAccessToOriginal()
    {
        return $this->_accessToOriginal;
    }
    
    
    /**
     * Sets whether the original file is published
     * 
     * @param boolean $publishOriginal
     */
    public function setPublishOriginal($publishOriginal)
    {
        $this->_publishOriginal = $publishOriginal;
    }
    
    
    /**
     * Returns whether the original file is published
     * 
     * @return boolean
     */
    public function getPublishOriginal()
    {
        return $this->_publishOriginal;
    }
    
    

    public function __toString()
    {
        return $this->getIdentifier();
    }

}