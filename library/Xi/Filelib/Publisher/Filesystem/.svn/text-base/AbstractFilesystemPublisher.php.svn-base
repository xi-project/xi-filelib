<?php

namespace Xi\Filelib\Publisher\Filesystem;

use \Xi\Filelib\Publisher\AbstractPublisher;

/**
 * Abstract filesystem publisher base class
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
abstract class AbstractFilesystemPublisher extends AbstractPublisher
{
    /**
     * @var integer Octal representation for directory permissions
     */
    private $_directoryPermission = 0700;

    /**
     * @var integer Octal representation for file permissions
     */
    private $_filePermission = 0600;
    
    
    /**
     * @var string Physical public root
     */
    private $_publicRoot;

    
    /**
     * Base url prepended to urls
     * 
     * @var string
     */
    private $_baseUrl = '';
    
    
    /**
     * Sets base url
     * 
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->_baseUrl = $baseUrl;
    }
    
    
    /**
     * Returns base url
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }
    
    
    
    /**
     * Sets public root
     *
     * @param string $publicRoot
     * @return \Xi\Filelib\FileLibrary Filelib
     */
    public function setPublicRoot($publicRoot)
    {
        $this->_publicRoot = $publicRoot;
        return $this;
    }


    /**
     * Returns public root
     *
     * @return string
     */
    public function getPublicRoot()
    {
        return $this->_publicRoot;
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
    
    
    public function getUrl(\Xi\Filelib\File\File $file)
    {
        $url = $this->getBaseUrl() . '/' . $file->getProfileObject()->getLinker()->getLink($file);
        return $url;
    }
    
    public function getUrlVersion(\Xi\Filelib\File\File $file, \Xi\Filelib\Plugin\VersionProvider\VersionProvider $version)
    {
        $url = $this->getBaseUrl() . '/' . $file->getProfileObject()->getLinker()->getLinkVersion($file, $version);
        return $url;
    }
    
    
    
    
    
    
}


