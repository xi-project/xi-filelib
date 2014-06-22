<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter;

use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator;
use Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator;
use Xi\Filelib\LogicException;
use Xi\Filelib\Storage\Retrieved;
use Xi\Filelib\Storage\Storable;
use Xi\Filelib\Version;

/**
 * Stores files in a filesystem
 *
 * @author pekkis
 */
class FilesystemStorageAdapter extends BaseStorageAdapter
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
        $filePermission = "600",
        $directoryPermission = "700"
    ) {

        if (!is_dir($root) || !is_writable($root)) {
            throw new LogicException("Root directory '{$root}' is not writable");
        }

        $this->root = $root;
        $this->directoryIdCalculator = $directoryIdCalculator ?: new TimeDirectoryIdCalculator();
        $this->filePermission = octdec($filePermission);
        $this->directoryPermission = octdec($directoryPermission);
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
    public function getDirectoryId(Storable $storable)
    {
        return $this->getDirectoryIdCalculator()->calculateDirectoryId($storable);
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

    private function getVersionPathName(Storable $storable, Version $version)
    {
        list($resource, $file) = $this->extractResourceAndFileFromStorable($storable);

        $path = $this->getRoot() . '/' . $this->getDirectoryId($resource) . '/' . $version->toString();
        if ($file) {
            $path .= '/sub/' . $resource->getId() . '/' . $this->getDirectoryId($file);
        }
        $path .= '/' . (($file) ? $file->getId() : $resource->getId());

        return $path;
    }

    public function store(Resource $resource, $tempFile)
    {
        $pathName = $this->getPathName($resource);

        if (!is_dir(dirname($pathName))) {
            // Sorry for the silencer but it is needed here
            @mkdir(dirname($pathName), $this->getDirectoryPermission(), true);
        }
        copy($tempFile, $pathName);
        chmod($pathName, $this->getFilePermission());
    }

    public function storeVersion(Storable $storable, Version $version, $tempFile)
    {
        $pathName = $this->getVersionPathName($storable, $version);

        if (!is_dir(dirname($pathName))) {
            // Sorry for the silencer but it is needed here
            @mkdir(dirname($pathName), $this->getDirectoryPermission(), true);
        }
        copy($tempFile, $pathName);
    }

    public function retrieve(Resource $resource)
    {
        return new Retrieved($this->getPathName($resource), false);
    }

    public function retrieveVersion(Storable $storable, Version $version)
    {
        return new Retrieved(
            $this->getVersionPathName($storable, $version), false
        );
    }

    public function delete(Resource $resource)
    {
        $path = $this->getPathName($resource);
        unlink($path);
    }

    public function deleteVersion(Storable $storable, Version $version)
    {
        $path = $this->getVersionPathName($storable, $version);
        unlink($path);
    }

    public function exists(Resource $resource)
    {
        return file_exists($this->getPathName($resource));
    }

    public function versionExists(Storable $storable, Version $version)
    {
        return file_exists($this->getVersionPathName($storable, $version));
    }
}
