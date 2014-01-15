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
use Xi\Filelib\LogicException;

use Gaufrette\Filesystem;

/**
 * Stores files in a filesystem
 *
 * @author pekkis
 */
class GaufretteStorage extends AbstractStorage implements Storage
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

    private function getVersionPathName(Resource $resource, $version, File $file = null)
    {
        $path = $this->getDirectoryId($resource) . '/' . $version;
        if ($file) {
            $path .= '/sub/' . $resource->getId() . '/' . $this->getDirectoryId($file);
        }
        $path .= '/' . (($file) ? $file->getId() : $resource->getId());

        return $path;
    }

    protected function doStore(Resource $resource, $tempFile)
    {
        $pathName = $this->getPathName($resource);
        $this->filesystem->write($pathName, file_get_contents($tempFile));
    }

    protected function doStoreVersion(Resource $resource, $version, $tempFile, File $file = null)
    {
        $pathName = $this->getVersionPathName($resource, $version, $file);
        $this->filesystem->write($pathName, file_get_contents($tempFile));
    }

    protected function doRetrieve(Resource $resource)
    {
        $tmp = $this->tempFiles->getTemporaryFilename();
        file_put_contents($tmp, $this->filesystem->get($this->getPathName($resource))->getContent());
        return $tmp;
    }

    protected function doRetrieveVersion(Resource $resource, $version, File $file = null)
    {
        $tmp = $this->tempFiles->getTemporaryFilename();
        file_put_contents(
            $tmp, $this->filesystem->get($this->getVersionPathName($resource, $version, $file))->getContent()
        );
        return $tmp;
    }

    protected function doDelete(Resource $resource)
    {
        $this->filesystem->delete($this->getPathName($resource));
    }

    protected function doDeleteVersion(Resource $resource, $version, File $file = null)
    {
        $this->filesystem->delete($this->getVersionPathName($resource, $version, $file));
    }

    public function exists(Resource $resource)
    {
        return $this->filesystem->has($this->getPathName($resource));
    }

    public function versionExists(Resource $resource, $version, File $file = null)
    {
        return $this->filesystem->has($this->getVersionPathName($resource, $version, $file));
    }
}
