<?php

namespace Xi\Filelib\Storage;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Storage\AbstractStorage;
use Xi\Filelib\File\File;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator;
use Xi\Filelib\FilelibException;

/**
 * Stores files in a filesystem
 * 
 * @author pekkis
 * @todo Fucktor caching to directoryIdCalculator
 *
 */
class FilesystemStorage extends AbstractStorage implements Storage
{
    /**
     * @var string Physical root
     */
    private $root;

    /**
     * @var integer Octal representation for directory permissions
     */
    private $directoryPermission = 0700;

    /**
     * @var integer Octal representation for file permissions
     */
    private $filePermission = 0600;
    
    /**
     * @var DirectoryIdCalculator
     */
    private $directoryIdCalculator;
    
    /**
     * @var boolean Do we cache calculated directory ids?
     */
    private $cacheDirectoryIds = true;
    
    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }
    
    /**
     * Sets caching of directory ids
     * 
     * @param boolean $cacheDirectoryIds
     * @return FilesystemStorage 
     */
    public function setCacheDirectoryIds($cacheDirectoryIds)
    {
        $this->cacheDirectoryIds = $cacheDirectoryIds;
        return $this;
    }

    /**
     * Returns whether caching of ids is turned on
     * 
     * @return boolean 
     */
    public function getCacheDirectoryIds()
    {
        return $this->cacheDirectoryIds;
    }
        
    
    /**
     * Sets directory id calculator
     * 
     * @param DirectoryIdCalculator $directoryIdCalculator
     * @return FilesystemStorage
     */
    public function setDirectoryIdCalculator(DirectoryIdCalculator $directoryIdCalculator)
    {
        $this->directoryIdCalculator = $directoryIdCalculator;
        return $this;
    }
    
    
    /**
     * Returns directory id calculator
     * 
     * @return DirectoryIdCalculator
     */
    public function getDirectoryIdCalculator()
    {
        return $this->directoryIdCalculator;
    }
    
    /**
     *
     * Returns directory id for a file
     * 
     * @param File $file
     * @return string 
     */
    public function getDirectoryId(File $file)
    {
        if(!$this->getCacheDirectoryIds()) {
            return $this->getDirectoryIdCalculator()->calculateDirectoryId($file);    
        }
        
        if(!isset($this->cache[$file->getId()])) {
            $this->cache[$file->getId()] = $this->getDirectoryIdCalculator()->calculateDirectoryId($file);
        }
        return $this->cache[$file->getId()];
    }
    
    

    /**
     * Sets directory permission
     *
     * @param integer $directoryPermission
     * @return FilesystemStorage
     */
    public function setDirectoryPermission($directoryPermission)
    {
        $this->directoryPermission = octdec($directoryPermission);
        return $this;
    }

    /**
     * Returns directory permission
     *
     * @return integer
     */
    public function getDirectoryPermission()
    {
        return $this->directoryPermission;
    }

    /**
     * Sets file permission
     *
     * @param integer $filePermission
     * @return FilesystemStorage
     */
    public function setFilePermission($filePermission)
    {
        $this->filePermission = octdec($filePermission);
        return $this;
    }

    /**
     * Returns file permission
     *
     * @return integer
     */
    public function getFilePermission()
    {
        return $this->filePermission;
    }

    /**
     * Sets root
     *
     * @param string $root
     * @return FilesystemStorage
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * Returns root
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }
    
    public function store(File $file, $tempFile)
    {
        $this->assertRootExistsAndIsWritable();
        
        $dir = $this->getRoot() . '/' . $this->getDirectoryId($file);

        if(!is_dir($dir)) {
            mkdir($dir, $this->getDirectoryPermission(), true);
        }
            
        $fileTarget = $dir . '/' . $file->getId();

        copy($tempFile, $fileTarget);
        chmod($fileTarget, $this->getFilePermission());
            
    }
    
    public function storeVersion(File $file, $version, $tempFile)
    {
        $this->assertRootExistsAndIsWritable();
        
        $path = $this->getRoot() . '/' . $this->getDirectoryId($file) . '/' . $version;
        
        if(!is_dir($path)) {
            mkdir($path, $this->getDirectoryPermission(), true);
        }
                 
        copy($tempFile, $path . '/' . $file->getId());
    }
    
    public function retrieve(File $file)
    {
        $path = $this->getRoot() . '/' . $this->getDirectoryId($file) . '/' . $file->getId();
        
        if(!is_file($path)) {
            throw new FilelibException('Could not retrieve file');
        }
        
        return new FileObject($path);
    }
    
    public function retrieveVersion(File $file, $version)
    {
        $path = $this->getRoot() . '/' . $this->getDirectoryId($file) . '/' . $version . '/' . $file->getId();
        
        if(!is_file($path)) {
            throw new FilelibException('Could not retrieve file');
        }
        
        return new FileObject($path);
    }
    
    public function delete(File $file)
    {
        $path = $this->getRoot() . '/' . $this->getDirectoryId($file) . '/' . $file->getId();
        
        if (is_file($path) && is_writable($path)) {
            unlink($path);
        }

    }
    
    
    public function deleteVersion(File $file, $version)
    {
        $path = $this->getRoot() . '/' . $this->getDirectoryId($file) . '/' . $version . '/' . $file->getId();
        
        if (is_file($path) && is_writable($path)) {
            unlink($path);
        }
        
    }
    
    
    private function assertRootExistsAndIsWritable()
    {
        if (!$root = $this->getRoot()) {
            throw new \LogicException('Root must be defined');
        }
                
        if (!is_dir($root) || !is_writable($root)) {
            throw new \LogicException('Defined root is not writable');
        }

    }
    
}