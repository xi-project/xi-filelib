<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter;

use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Tool\PathCalculator\ImprovedPathCalculator;
use Xi\Filelib\Storage\FileIOException;
use Xi\Filelib\Tool\PathCalculator\PathCalculator;
use Xi\Filelib\Storage\Retrieved;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Versionable\Versionable;

/**
 * Stores files in a filesystem
 *
 * @author pekkis
 * @deprecated Use FlysystemAdapter
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
     * @var PathCalculator
     */
    private $pathCalculator;

    public function __construct(
        $root,
        PathCalculator $pathCalculator = null,
        $filePermission = "600",
        $directoryPermission = "700"
    ) {

        if (!is_dir($root) || !is_writable($root)) {
            throw new FileIOException("Root directory '{$root}' is not writable");
        }

        $this->root = $root;
        $this->pathCalculator = $pathCalculator ?: new ImprovedPathCalculator();
        $this->filePermission = octdec($filePermission);
        $this->directoryPermission = octdec($directoryPermission);
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

    private function getPathName(ConcreteResource $resource)
    {
        return $this->getRoot() . '/' . $this->pathCalculator->getPath($resource);
    }

    /**
     * @param ConcreteResource $resource
     * @param string $tempFile
     */
    public function store(ConcreteResource $resource, $tempFile)
    {
        $pathName = $this->getPathName($resource);

        if (!is_dir(dirname($pathName))) {
            $this->createDirectory(dirname($pathName));
        }
        copy($tempFile, $pathName);
        chmod($pathName, $this->getFilePermission());

        return new Retrieved($pathName, false);
    }

    public function retrieve(ConcreteResource $resource)
    {
        return new Retrieved($this->getPathName($resource), false);
    }

    public function delete(ConcreteResource $resource)
    {
        $path = $this->getPathName($resource);
        unlink($path);
    }

    public function exists(ConcreteResource $resource)
    {
        return file_exists($this->getPathName($resource));
    }

    /**
     * @param $dir
     * @throws FileIOException
     */
    private function createDirectory($dir)
    {
        $created = @mkdir($dir, $this->getDirectoryPermission(), true);
        if (!$created) {
            throw new FileIOException(
                sprintf(
                    "Directory '%s' could not be created",
                    $dir
                )
            );
        }
    }
}
