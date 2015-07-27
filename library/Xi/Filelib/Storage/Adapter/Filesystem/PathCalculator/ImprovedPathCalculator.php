<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter\Filesystem\PathCalculator;

use Pekkis\DirectoryCalculator\Strategy\UniversalLeveledStrategy;
use Pekkis\DirectoryCalculator\DirectoryCalculator;
use Xi\Filelib\Version;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Versionable;
use Xi\Filelib\File\File;
use Closure;

class ImprovedPathCalculator implements PathCalculator
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var DirectoryCalculator
     */
    private $directoryIdCalculator;

    /**
     * @param DirectoryCalculator $directoryIdCalculator
     * @param Closure $callback
     */
    public function __construct(DirectoryCalculator $directoryIdCalculator = null, $prefix = '')
    {
        $this->directoryIdCalculator = $directoryIdCalculator ?: new DirectoryCalculator(
            new UniversalLeveledStrategy()
        );
        $this->prefix = trim($prefix, '/');
    }

    /**
     * @param Resource $resource
     * @return string
     */
    public function getPath(Resource $resource)
    {
        return $this->getPrefix() . 'resources/' . $this->directoryIdCalculator->calculateDirectory($resource) . '/' . $resource->getId();
    }

    /**
     * @return string
     */
    private function getPrefix()
    {
        return (!$this->prefix) ? '' : $this->prefix . '/';
    }

    /**
     * @param Versionable $versionable
     * @param Version $version
     * @return string
     */
    public function getPathVersion(Versionable $versionable, Version $version)
    {
        list($resource, $file) = $this->extractResourceAndFileFromVersionable($versionable);

        $path = $this->getPrefix();

        if ($file) {
            $path .= 'files/';
        } else {
            $path .= 'resources/';
        }

        $path .= $this->directoryIdCalculator->calculateDirectory($file ?: $resource) . '/' . $version->toString();
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
