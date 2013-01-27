<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher\Filesystem;

use Xi\Filelib\Publisher\AbstractPublisher;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\File\File;
use Xi\Filelib\Linker\Linker;
use LogicException;
use SplFileInfo;
use Xi\Filelib\File\FileOperator;

/**
 * Abstract filesystem publisher convenience class
 *
 */
abstract class AbstractFilesystemPublisher extends AbstractPublisher
{
    /**
     * @var FileOperator
     */
    protected $fileOperator;

    /**
     * @var integer Octal representation for directory permissions
     */
    private $directoryPermission = 0700;

    /**
     * @var integer Octal representation for file permissions
     */
    private $filePermission = 0600;

    /**
     * @var string Physical public root
     */
    private $publicRoot;

    /**
     * Base url prepended to urls
     *
     * @var string
     */
    private $baseUrl = '';

    /**
     * @param FileOperator $fileOperator
     * @param array        $options
     */
    public function __construct(FileOperator $fileOperator, $options = array())
    {
        parent::__construct($options);
        $this->fileOperator = $fileOperator;
    }

    /**
     * Sets base url
     *
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Returns base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Sets public root
     *
     * @param string $publicRoot
     * AbstractFilesystemPublisher
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

        $this->publicRoot = $dir->getRealPath();

        return $this;
    }

    /**
     * Returns public root
     *
     * @return string
     */
    public function getPublicRoot()
    {
        return $this->publicRoot;
    }

    /**
     * Sets directory permission
     *
     * @param  integer                     $directoryPermission
     * @return AbstractFilesystemPublisher
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
     * AbstractFilesystemPublisher
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
     * Returns linker for a file
     *
     * @param  File   $file
     * @return Linker
     */
    public function getLinkerForFile(File $file)
    {
        return $this->fileOperator->getProfile($file->getProfile())->getLinker();
    }

    /**
     * @param  File   $file
     * @return string
     */
    public function getUrl(File $file)
    {
        $url = $this->getBaseUrl() . '/' . $this->getLinkerForFile($file)->getLink($file);

        return $url;
    }

    /**
     * @param  File            $file
     * @param  string          $version
     * @param  VersionProvider $versionProvider
     * @return string
     */
    public function getUrlVersion(File $file, $version, VersionProvider $versionProvider)
    {
        $url = $this->getBaseUrl() . '/';
        $url .= $this
            ->getLinkerForFile($file)
            ->getLinkVersion($file, $version, $versionProvider->getExtensionFor($version));

        return $url;
    }
}
