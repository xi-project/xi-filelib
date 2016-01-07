<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\FileIOException;
use Xi\Filelib\Tool\PathCalculator\PathCalculator;
use Xi\Filelib\Tool\PathCalculator\ImprovedPathCalculator;
use Xi\Filelib\Storage\Retrieved;
use Xi\Filelib\Version;
use Xi\Filelib\Versionable;

/**
 * Stores files in a filesystem
 *
 * @author pekkis
 */
class FlysystemStorageAdapter extends BaseTemporaryRetrievingStorageAdapter
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var PathCalculator
     */
    private $pathCalculator;

    /**
     * @param Filesystem $filesystem
     * @param PathCalculator $pathCalculator
     */
    public function __construct(
        Filesystem $filesystem,
        PathCalculator $pathCalculator = null
    ) {

        $this->filesystem = $filesystem;
        $this->pathCalculator = ($pathCalculator) ?: new ImprovedPathCalculator();
    }

    private function getPathName(Resource $resource)
    {
        return $this->pathCalculator->getPath($resource);
    }

    private function getVersionPathName(Versionable $versionable, Version $version)
    {
        return $this->pathCalculator->getPathVersion($versionable, $version);
    }

    public function store(Resource $resource, $tempFile)
    {
        $pathName = $this->getPathName($resource);
        $ret = $this->filesystem->put(
            $pathName,
            file_get_contents($tempFile),
            [
                'visibility' => AdapterInterface::VISIBILITY_PRIVATE
            ]
        );

        if (!$ret) {
            throw new FileIOException(
                sprintf('Failed to store resource #%s', $resource->getId())
            );
        }

        return new Retrieved($tempFile);
    }

    public function storeVersion(Versionable $versionable, Version $version, $tempFile)
    {
        $pathName = $this->getVersionPathName($versionable, $version);

        $ret = $this->filesystem->put(
            $pathName,
            file_get_contents($tempFile),
            [
                'visibility' => AdapterInterface::VISIBILITY_PRIVATE
            ]
        );

        if (!$ret) {
            throw new FileIOException(
                sprintf(
                    "Failed to store version '%s' of versionable %s;%s",
                    $version->toString(),
                    get_class($versionable),
                    $versionable->getId()
                )
            );
        }

        return new Retrieved($tempFile);
    }

    public function retrieve(Resource $resource)
    {
        $ret = $this->filesystem->get($this->getPathName($resource));
        if (!$ret) {
            throw new FileIOException(
                sprintf('Failed to retrieve resource #%s', $resource->getId())
            );
        }

        return new Retrieved(
            $this->tempFiles->add(
                $ret->read()
            )
        );
    }

    public function retrieveVersion(Versionable $versionable, Version $version)
    {
        $ret = $this->filesystem->get($this->getVersionPathName($versionable, $version));
        if (!$ret) {
            throw new FileIOException(
                sprintf(
                    "Failed to retrieve version '%s' of versionable %s;%s",
                    $version->toString(),
                    get_class($versionable),
                    $versionable->getId()
                )
            );
        }

        return new Retrieved(
            $this->tempFiles->add(
                $ret->read()
            )
        );
    }

    public function delete(Resource $resource)
    {
        $ret = $this->filesystem->delete($this->getPathName($resource));
        if (!$ret) {
            throw new FileIOException(
                sprintf('Failed to delete resource #%s', $resource->getId())
            );
        }
    }

    public function deleteVersion(Versionable $versionable, Version $version)
    {
        $ret = $this->filesystem->delete($this->getVersionPathName($versionable, $version));
        if (!$ret) {
            throw new FileIOException(
                sprintf(
                    "Failed to delete version '%s' of versionable %s;%s",
                    $version->toString(),
                    get_class($versionable),
                    $versionable->getId()
                )
            );
        }
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
