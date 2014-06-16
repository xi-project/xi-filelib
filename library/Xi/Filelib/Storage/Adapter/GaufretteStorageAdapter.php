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
use Xi\Filelib\Identifiable;
use Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\TimeDirectoryIdCalculator;
use Xi\Filelib\LogicException;

use Gaufrette\Filesystem;
use Xi\Filelib\Storage\Storable;

/**
 * Stores files in a filesystem
 *
 * @author pekkis
 */
class GaufretteStorageAdapter extends BaseStorageAdapter
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var TemporaryFileContainer
     */
    private $tempFiles;

    /**
     * @var DirectoryIdCalculator
     */
    private $directoryIdCalculator;

    /**
     * @param Filesystem $filesystem
     * @param DirectoryIdCalculator $directoryIdCalculator
     * @param string $tempDir
     */
    public function __construct(
        Filesystem $filesystem,
        DirectoryIdCalculator $directoryIdCalculator = null,
        $tempDir = null
    ) {

        $this->filesystem = $filesystem;
        $this->tempFiles = new TemporaryFileContainer($tempDir);

        $this->directoryIdCalculator = $directoryIdCalculator ?: new TimeDirectoryIdCalculator();
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

    private function getPathName(Resource $resource)
    {
        $dir = $this->getDirectoryId($resource);
        $fileTarget = $dir . '/' . $resource->getId();

        return $fileTarget;
    }

    private function getVersionPathName(Storable $storable, $version)
    {
        list($resource, $file) = $this->extractResourceAndFileFromStorable($storable);

        $path = $this->getDirectoryId($resource) . '/' . $version;
        if ($file) {
            $path .= '/sub/' . $resource->getId() . '/' . $this->getDirectoryId($file);
        }
        $path .= '/' . (($file) ? $file->getId() : $resource->getId());

        return $path;
    }

    public function store(Resource $resource, $tempFile)
    {
        $pathName = $this->getPathName($resource);
        $this->filesystem->write($pathName, file_get_contents($tempFile));
    }

    public function storeVersion(Storable $storable, $version, $tempFile)
    {
        $pathName = $this->getVersionPathName($storable, $version);
        $this->filesystem->write($pathName, file_get_contents($tempFile));
    }

    public function retrieve(Resource $resource)
    {
        $tmp = $this->tempFiles->getTemporaryFilename();
        file_put_contents($tmp, $this->filesystem->get($this->getPathName($resource))->getContent());
        return $tmp;
    }

    public function retrieveVersion(Storable $storable, $version)
    {
        $tmp = $this->tempFiles->getTemporaryFilename();
        file_put_contents(
            $tmp,
            $this->filesystem->get(
                $this->getVersionPathName($storable, $version)
            )->getContent()
        );
        return $tmp;
    }

    public function delete(Resource $resource)
    {
        $this->filesystem->delete($this->getPathName($resource));
    }

    public function deleteVersion(Storable $storable, $version)
    {
        $this->filesystem->delete($this->getVersionPathName($storable, $version));
    }

    public function exists(Resource $resource)
    {
        return $this->filesystem->has($this->getPathName($resource));
    }

    public function versionExists(Storable $storable, $version)
    {
        return $this->filesystem->has($this->getVersionPathName($storable, $version));
    }
}
