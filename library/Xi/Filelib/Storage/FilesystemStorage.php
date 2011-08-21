<?php

namespace Xi\Filelib\Storage;

/**
 * Stores files in a filesystem
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
class FilesystemStorage extends \Xi\Filelib\Storage\AbstractStorage implements \Xi\Filelib\Storage\Storage
{
    /**
     * @var string Physical root
     */
    private $_root;

    /**
     * @var integer Octal representation for directory permissions
     */
    private $_directoryPermission = 0700;

    /**
     * @var integer Octal representation for file permissions
     */
    private $_filePermission = 0600;
    
    /**
     * @var \Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator
     */
    private $_directoryIdCalculator;
    
    /**
     * @var boolean Do we cache calculated directory ids?
     */
    private $_cacheDirectoryIds = true;
    
    public function __construct($options = array())
    {
        \Xi\Filelib\Configurator::setConstructorOptions($this, $options);
    }
    
    
    public function setCacheDirectoryIds($cacheDirectoryIds)
    {
        $this->_cacheDirectoryIds = $cacheDirectoryIds;
    }
    
    
    public function getCacheDirectoryIds()
    {
        return $this->_cacheDirectoryIds;
    }
        
    
    /**
     * Sets directory id calculator
     * 
     * @param Filesystem\DirectoryIdCalculator\DirectoryIdCalculator $directoryIdCalculator
     */
    public function setDirectoryIdCalculator(Filesystem\DirectoryIdCalculator\DirectoryIdCalculator $directoryIdCalculator)
    {
        $this->_directoryIdCalculator = $directoryIdCalculator;
    }
    
    
    /**
     * Returns directory id calculator
     * 
     * @return \Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator
     */
    public function getDirectoryIdCalculator()
    {
        return $this->_directoryIdCalculator;
    }
    
    
    public function getDirectoryId(\Xi\Filelib\File\File $file)
    {
        if(!$this->getCacheDirectoryIds()) {
            return $this->getDirectoryIdCalculator()->calculateDirectoryId($file);    
        }
        
        if(!isset($this->_cache[$file->getId()])) {
            $this->_cache[$file->getId()] = $this->getDirectoryIdCalculator()->calculateDirectoryId($file);
        }
        return $this->_cache[$file->getId()];
    }
    
    

    /**
     * Sets directory permission
     *
     * @param integer $directoryPermission
     * @return \Xi\Filelib\FileLibrary Filelib
     */
    public function setDirectoryPermission($directoryPermission)
    {
        $this->_directoryPermission = octdec($directoryPermission);
        return $this;
    }

    /**
     * Returns directory permission
     *
     * @return integer
     */
    public function getDirectoryPermission()
    {
        return $this->_directoryPermission;
    }

    /**
     * Sets file permission
     *
     * @param integer $filePermission
     * @return \Xi\Filelib\FileLibrary Filelib
     */
    public function setFilePermission($filePermission)
    {
        $this->_filePermission = octdec($filePermission);
        return $this;
    }

    /**
     * Returns file permission
     *
     * @return integer
     */
    public function getFilePermission()
    {
        return $this->_filePermission;
    }

    /**
     * Sets root
     *
     * @param string $root
     * @return \Xi\Filelib\FileLibrary Filelib
     */
    public function setRoot($root)
    {
        $this->_root = $root;
    }

    /**
     * Returns root
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->_root;
    }
    
    public function store(\Xi\Filelib\File\File $file, $tempFile)
    {
        $root = $this->getRoot();
        $dir = $root . '/' . $this->getDirectoryId($file);

        if(!is_dir($dir)) {
            @mkdir($dir, $this->getDirectoryPermission(), true);
        }

        if(!is_dir($dir) || !is_writable($dir)) {
            throw new \Xi\Filelib\FilelibException("Could not write into directory", 500);
        }
            
        $fileTarget = $dir . '/' . $file->getId();

        copy($tempFile, $fileTarget);
        chmod($fileTarget, $this->getFilePermission());
            
        if(!is_readable($fileTarget)) {
            throw new \Xi\Filelib\FilelibException('Could not copy file to folder');
        }
    }
    
    public function storeVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version, $tempFile)
    {
        $path = $this->getRoot() . '/' . $this->getDirectoryId($file) . '/' . $version->getIdentifier();
                 
        if(!is_dir($path)) {
            mkdir($path, $this->getDirectoryPermission(), true);
        }
                 
        copy($tempFile, $path . '/' . $file->getId());
    }
    
    public function retrieve(\Xi\Filelib\File\File $file)
    {
        $path = $this->getRoot() . '/' . $this->getDirectoryId($file) . '/' . $file->getId();
        
        if(!is_file($path)) {
            throw new \Xi\Filelib\FilelibException('Could not retrieve file');
        }
        
        return new \Xi\Filelib\File\FileObject($path);
    }
    
    public function retrieveVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version)
    {
        $path = $this->getRoot() . '/' . $this->getDirectoryId($file) . '/' . $version->getIdentifier() . '/' . $file->getId();
        
        if(!is_file($path)) {
            throw new \Xi\Filelib\FilelibException('Could not retrieve file');
        }
        
        return new \Xi\Filelib\File\FileObject($path);
    }
    
    public function delete(\Xi\Filelib\File\File $file)
    {
        $path = $this->getRoot() . '/' . $this->getDirectoryId($file) . '/' . $file->getId();
            
        $fileObj = new \SplFileObject($path);
        
        if(!$fileObj->isFile() || !$fileObj->isWritable()) {
            throw new \Xi\Filelib\FilelibException('Can not delete file');
        }
        if(!@unlink($fileObj->getPathname())) {
            throw new \Xi\Filelib\FilelibException('Can not delete file');
        }
    }
    
    
    public function deleteVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version)
    {
        $path = $this->getRoot() . '/' . $this->getDirectoryId($file) . '/' . $version->getIdentifier() . '/' . $file->getId();
        unlink($path);
    }
}