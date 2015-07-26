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
use Xi\Filelib\Storage\Adapter\Filesystem\PathCalculator\PathCalculator;
use Xi\Filelib\Storage\Adapter\Filesystem\PathCalculator\ImprovedPathCalculator;
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
        $this->filesystem->put(
            $pathName,
            file_get_contents($tempFile),
            [
                'visibility' => AdapterInterface::VISIBILITY_PRIVATE
            ]
        );

        return new Retrieved($tempFile);
    }

    public function storeVersion(Versionable $versionable, Version $version, $tempFile)
    {
        $pathName = $this->getVersionPathName($versionable, $version);

        $this->filesystem->put(
            $pathName,
            file_get_contents($tempFile),
            [
                'visibility' => AdapterInterface::VISIBILITY_PRIVATE
            ]
        );
        return new Retrieved($tempFile);
    }

    public function retrieve(Resource $resource)
    {
        return new Retrieved(
            $this->tempFiles->add(
                $this->filesystem->get($this->getPathName($resource))->read()
            )
        );
    }

    public function retrieveVersion(Versionable $versionable, Version $version)
    {
        return new Retrieved(
            $this->tempFiles->add(
                $this->filesystem->get($this->getVersionPathName($versionable, $version))->read()
            )
        );
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
