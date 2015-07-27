<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter\Filesystem\PathCalculator;

use Pekkis\DirectoryCalculator\DirectoryCalculator;
use Pekkis\DirectoryCalculator\Strategy\UniversalLeveledStrategy;
use Xi\Filelib\Version;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Versionable;
use Xi\Filelib\File\File;
use Closure;

class LegacyPathCalculator implements PathCalculator
{
    /**
     * @var DirectoryCalculator
     */
    private $directoryIdCalculator;

    /**
     * @param DirectoryCalculator $directoryIdCalculator
     * @param Closure $callback
     */
    public function __construct(DirectoryCalculator $directoryIdCalculator = null)
    {
        $this->directoryIdCalculator = $directoryIdCalculator ?: new DirectoryCalculator(
            new UniversalLeveledStrategy()
        );
    }

    /**
     * @param Resource $resource
     * @return string
     */
    public function getPath(Resource $resource)
    {
        return $this->directoryIdCalculator->calculateDirectory($resource) . '/' . $resource->getId();
    }

    /**
     * @param Versionable $versionable
     * @param Version $version
     * @return string
     */
    public function getPathVersion(Versionable $versionable, Version $version)
    {
        list($resource, $file) = $this->extractResourceAndFileFromVersionable($versionable);
        $path = $this->directoryIdCalculator->calculateDirectory($resource) . '/' . $version->toString();
        if ($file) {
            $path .= '/sub/' . $resource->getId() . '/' . $this->directoryIdCalculator->calculateDirectory($file);
        }
        $path .= '/' . (($file) ? $file->getId() : $resource->getId());
        return $path;
    }

    /**
     * @param Versionable $versionable
     * @return array Tuple of storage and file (or null)
     */
    protected function extractResourceAndFileFromVersionable(Versionable $versionable)
    {
        if ($versionable instanceof File) {
            $file = $versionable;
            $resource = $file->getResource();
        } else {
            $resource = $versionable;
            $file = null;
        }

        return [$resource, $file];
    }
}
