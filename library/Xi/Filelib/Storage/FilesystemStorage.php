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
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator;
use Xi\Filelib\IdentityMap\Identifiable;
use Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator;

/**
 * Stores files in a filesystem
 *
 * @author pekkis
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

    public function __construct(
        $root,
        DirectoryIdCalculator $directoryIdCalculator = null,
        $filePermission = 0600,
        $directoryPermission = 0700
    ) {

        if (!is_dir($root) || !is_writable($root)) {
            throw new \LogicException("Root directory '{$root}' is not writable");
        }

        $this->root = $root;
        $this->directoryIdCalculator = $directoryIdCalculator ?: new TimeDirectoryIdCalculator();
        $this->filePermission = $filePermission;
        $this->directoryPermission = $directoryPermission;
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
    public function getDirectoryId(Identifiable $identifiable)
    {
        return $this->getDirectoryIdCalculator()->calculateDirectoryId($identifiable);
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
     * Returns file permission
     *
     * @return integer
     */
    public function getFilePermission()
    {
        return $this->filePermission;
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
        $pathName = $this->getVersionPathName($resource, $version, $file);

        if (!is_dir(dirname($pathName))) {
            // Sorry for the silencer but it is needed here
            @mkdir(dirname($pathName), $this->getDirectoryPermission(), true);
        }
        copy($tempFile, $pathName);
    }

    protected function doRetrieve(Resource $resource)
    {
        return $this->getPathName($resource);
    }

    protected function doRetrieveVersion(Resource $resource, $version, File $file = null)
    {
        return $this->getVersionPathName($resource, $version, $file);
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

    public function exists(Resource $resource)
    {
        return file_exists($this->getPathName($resource));
    }

    public function versionExists(Resource $resource, $version, File $file = null)
    {
        return file_exists($this->getVersionPathName($resource, $version, $file));
    }

}
