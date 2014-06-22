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
use Gaufrette\Filesystem;
use Xi\Filelib\Storage\Retrieved;
use Xi\Filelib\Storage\Versionable;
use Xi\Filelib\Version;

/**
 * Stores files in a filesystem
 *
 * @author pekkis
 */
class GaufretteStorageAdapter extends BaseTemporaryRetrievingStorageAdapter
{
    /**
     * @var Filesystem
     */
    private $filesystem;

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

    private function getVersionPathName(Versionable $versionable, Version $version)
    {
        list($resource, $file) = $this->extractResourceAndFileFromVersionable($versionable);

        $path = $this->getDirectoryId($resource) . '/' . $version->toString();
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

    public function storeVersion(Versionable $versionable, Version $version, $tempFile)
    {
        $pathName = $this->getVersionPathName($versionable, $version);
        $this->filesystem->write($pathName, file_get_contents($tempFile));
    }

    public function retrieve(Resource $resource)
    {
        $tmp = $this->getTemporaryFilename();
        file_put_contents($tmp, $this->filesystem->get($this->getPathName($resource))->getContent());
        return new Retrieved($tmp, true);
    }

    public function retrieveVersion(Versionable $versionable, Version $version)
    {
        $tmp = $this->getTemporaryFilename();
        file_put_contents(
            $tmp,
            $this->filesystem->get(
                $this->getVersionPathName($versionable, $version)
            )->getContent()
        );
        return new Retrieved($tmp, true);
    }

    public function delete(Resource $resource)
    {
        $this->filesystem->delete($this->getPathName($resource));
    }

    public function deleteVersion(Versionable $versionable, Version $version)
    {
        $this->filesystem->delete($this->getVersionPathName($versionable, $version));
    }

    public function exists(Resource $resource)
    {
        return $this->filesystem->has($this->getPathName($resource));
    }

    public function versionExists(Versionable $versionable, Version $version)
    {
        return $this->filesystem->has($this->getVersionPathName($versionable, $version));
    }
}
