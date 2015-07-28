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
use Xi\Filelib\Tool\PathCalculator\PathCalculator;
use Xi\Filelib\Tool\PathCalculator\ImprovedPathCalculator;
use Xi\Filelib\Storage\Retrieved;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Versionable\Versionable;

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

    public function retrieve(Resource $resource)
    {
        return new Retrieved(
            $this->tempFiles->add(
                $this->filesystem->get($this->getPathName($resource))->read()
            )
        );
    }

    public function delete(Resource $resource)
    {
        $this->filesystem->delete($this->getPathName($resource));
    }

    public function exists(Resource $resource)
    {
        return $this->filesystem->has($this->getPathName($resource));
    }
}
