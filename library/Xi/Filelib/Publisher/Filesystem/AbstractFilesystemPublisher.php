<?php

namespace Xi\Filelib\Publisher\Filesystem;

use Xi\Filelib\Publisher\AbstractPublisher;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\File\File;
use Xi\Filelib\Linker\Linker;
use LogicException;
use SplFileInfo;


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
        $dir = new SplFileInfo($publicRoot);

        if (!$dir->isDir()) {
            throw new LogicException("Directory '{$publicRoot}' does not exist");
        }

        if (!$dir->isWritable()) {
            throw new LogicException("Directory '{$publicRoot}' is not writeable");
        }

        $this->_publicRoot = $dir->getRealPath();
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

    /**
     * Returns file's linker
     *
     * @param File $file
     * @return Linker
     */
    public function getLinkerForFile(File $file)
    {
        return $this->getFilelib()->getFileOperator()->getProfile($file->getProfile())->getLinker();
    }


    public function getUrl(File $file)
    {
        $url = $this->getBaseUrl() . '/' . $this->getLinkerForFile($file)->getLink($file);
        return $url;
    }

    public function getUrlVersion(File $file, $version, VersionProvider $versionProvider)
    {
        $url = $this->getBaseUrl() . '/' . $this->getLinkerForFile($file)->getLinkVersion($file, $version, $versionProvider->getExtensionFor($version));
        return $url;
    }






}


