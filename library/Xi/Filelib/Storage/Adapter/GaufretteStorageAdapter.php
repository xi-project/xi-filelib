<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter;

use Gaufrette\Filesystem;
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
class GaufretteStorageAdapter extends BaseTemporaryRetrievingStorageAdapter
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
     * @param string $tempDir
     */
    public function __construct(
        Filesystem $filesystem,
        PathCalculator $pathCalculator = null,
        $tempDir = null
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

        if ($this->exists($resource)) {
            $this->delete($resource);
        }

        $this->filesystem->write($pathName, file_get_contents($tempFile), true);
    }

    public function storeVersion(Versionable $versionable, Version $version, $tempFile)
    {
        $pathName = $this->getVersionPathName($versionable, $version);

        if ($this->versionExists($versionable, $version)) {
            $this->deleteVersion($versionable, $version);
        }

        $this->filesystem->write($pathName, file_get_contents($tempFile), true);
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
