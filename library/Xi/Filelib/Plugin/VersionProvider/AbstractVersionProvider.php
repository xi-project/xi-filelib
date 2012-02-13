<?php

namespace Xi\Filelib\Plugin\VersionProvider;

/**
 * Abstract convenience class for version provider plugins
 *
 * @package Xi_Filelib
 * @author pekkis
 *
 */
abstract class AbstractVersionProvider extends \Xi\Filelib\Plugin\AbstractPlugin implements \Xi\Filelib\Plugin\VersionProvider\VersionProvider
{
    /**
     * @var string Version identifier
     */
    protected $_identifier;

    /**
     * @var array Array of file types for which the plugin provides a version
     */
    protected $_providesFor = array();

    /**
     * @var File extension for the version
     */
    protected $_extension;
    
    
    
    abstract public function createVersion(\Xi\Filelib\File\File $file);
    
    
    /**
     * Registers a version to all profiles
     */
    public function init()
    {
        if(!$this->getIdentifier()) {
            throw new \Xi\Filelib\FilelibException('Version plugin must have an identifier');
        }

        if(!$this->getExtension()) {
            throw new \Xi\Filelib\FilelibException('Version plugin must have a file extension');
        }

        foreach($this->getProvidesFor() as $fileType) {
            foreach($this->getProfiles() as $profile) {
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
        $this->_identifier = $identifier;
        return $this;
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
     * Sets file types for this version plugin.
     *
     * @param array $providesFor Array of file types
     * @return VersionProvider
     */
    public function setProvidesFor(array $providesFor)
    {
        $this->_providesFor = $providesFor;
        return $this;
    }

    /**
     * Returns file types which the version plugin provides version for.
     *
     * @return array
     */
    public function getProvidesFor()
    {
        return $this->_providesFor;
    }

    /**
     * Returns whether the plugin provides a version for a file.
     *
     * @param \Xi\Filelib\File\File $file File item
     * @return boolean
     */
    public function providesFor(\Xi\Filelib\File\File $file)
    {
        if(in_array($this->getFilelib()->getFileOperator()->getType($file), $this->getProvidesFor())) {
            if(in_array($file->getProfile(), $this->getProfiles())) {
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
        $this->_extension = $extension;
        return $this;
    }

    /**
     * Returns the plugins file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->_extension;
    }

        
    public function afterUpload(\Xi\Filelib\File\File $file)
    {
        if(!$this->providesFor($file)) {
            return;
        }
        $tmp = $this->createVersion($file);
        $this->getFilelib()->getStorage()->storeVersion($file, $this, $tmp);
        unlink($tmp);
    }


    public function onPublish(\Xi\Filelib\File\File $file)
    {
        if(!$this->providesFor($file)) {
            return;
        }

        $this->getFilelib()->getPublisher()->publishVersion($file, $this);

    }

    
    public function onUnpublish(\Xi\Filelib\File\File $file)
    {
        if(!$this->providesFor($file)) {
            return;
        }

        $this->getFilelib()->getPublisher()->unpublishVersion($file, $this);
        
    }
    
    public function onDelete(\Xi\Filelib\File\File $file)
    {
        if(!$this->providesFor($file)) {
            return;
        }
        
        $this->deleteVersion($file);

    }
    
    /**
     * Deletes a version
     * 
     * @param $file \Xi\Filelib\File\File
     * 
     */
    public function deleteVersion(\Xi\Filelib\File\File $file)
    {
        $this->getFilelib()->getStorage()->deleteVersion($file, $this);
    }

    
    


}
