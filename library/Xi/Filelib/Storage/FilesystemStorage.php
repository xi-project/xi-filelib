<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Storage\AbstractStorage;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator;

/**
 * Stores files in a filesystem
 *
 * @author pekkis
 * @todo Fucktor caching to directoryIdCalculator
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
     * @param  boolean           $cacheDirectoryIds
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
     * @param  DirectoryIdCalculator $directoryIdCalculator
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
     * Returns directory id for a file
     *
     * @param  Resource $resource
     * @return string
     */
    public function getDirectoryId($resource)
    {
        if (!$this->getCacheDirectoryIds()) {
            return $this->getDirectoryIdCalculator()->calculateDirectoryId($resource);
        }

        if (!isset($this->cache[$resource->getId()])) {
            $this->cache[$resource->getId()] = $this->getDirectoryIdCalculator()->calculateDirectoryId($resource);
        }

        return $this->cache[$resource->getId()];
    }

    /**
     * Sets directory permission
     *
     * @param  integer           $directoryPermission
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
     * @param  integer           $filePermission
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
     * @param  string            $root
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

    private function getPathName(Resource $resource)
    {
        $dir = $this->getRoot() . '/' . $this->getDirectoryId($resource);
        $fileTarget = $dir . '/' . $resource->getId();

        return $fileTarget;
    }

    private function getVersionPathName(Resource $resource, $version, File $file = null)
    {
        $path = $this->getRoot() . '/' . $this->getDirectoryId($resource) . '/' . $version;
        if ($file) {
            $path .= '/sub/' . $resource->getId() . '/' . $this->getDirectoryId($file);
        }
        $path .= '/' . (($file) ? $file->getId() : $resource->getId());

        return $path;
    }

    protected function doStore(Resource $resource, $tempFile)
    {
        $this->assertRootExistsAndIsWritable();
        $pathName = $this->getPathName($resource);

        if (!is_dir(dirname($pathName))) {
            // Sorry for the silencer but it is needed here
            @mkdir(dirname($pathName), $this->getDirectoryPermission(), true);
        }
        copy($tempFile, $pathName);
        chmod($pathName, $this->getFilePermission());
    }

    protected function doStoreVersion(Resource $resource, $version, $tempFile, File $file = null)
    {
        $this->assertRootExistsAndIsWritable();
        $pathName = $this->getVersionPathName($resource, $version, $file);

        if (!is_dir(dirname($pathName))) {
            // Sorry for the silencer but it is needed here
            @mkdir(dirname($pathName), $this->getDirectoryPermission(), true);
        }
        copy($tempFile, $pathName);
    }

    protected function doRetrieve(Resource $resource)
    {
        return new FileObject($this->getPathName($resource));
    }

    protected function doRetrieveVersion(Resource $resource, $version, File $file = null)
    {
        return new FileObject($this->getVersionPathName($resource, $version, $file));
    }

    protected function doDelete(Resource $resource)
    {
        $path = $this->getPathName($resource);
        unlink($path);
    }

    protected function doDeleteVersion(Resource $resource, $version, File $file = null)
    {
        $path = $this->getVersionPathName($resource, $version, $file);
        unlink($path);
    }

    public function assertRootExistsAndIsWritable()
    {
        if (!$root = $this->getRoot()) {
            throw new \LogicException('Root must be defined');
        }

        if (!is_dir($root) || !is_writable($root)) {
            throw new \LogicException('Defined root is not writable');
        }
    }

    public function exists(Resource $resource)
    {
        return file_exists($this->getPathName($resource));
    }

    public function versionExists(Resource $resource, $version, File $file = null)
    {
        return file_exists($this->getVersionPathName($resource, $version, $file));
    }

}
