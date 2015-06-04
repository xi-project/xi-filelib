<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter\Filesystem\PathCalculator;

use Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\UniversalLeveledDirectoryIdCalculator;
use Xi\Filelib\Version;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator;
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
     * @var DirectoryIdCalculator
     */
    private $directoryIdCalculator;

    /**
     * @param DirectoryIdCalculator $directoryIdCalculator
     * @param Closure $callback
     */
    public function __construct(DirectoryIdCalculator $directoryIdCalculator = null, $prefix = '')
    {
        $this->directoryIdCalculator = $directoryIdCalculator ?: new UniversalLeveledDirectoryIdCalculator();
        $this->prefix = $prefix;
    }

    /**
     * @param Resource $resource
     * @return string
     */
    public function getPath(Resource $resource)
    {
        return $this->getPrefix() . 'resources/' . $this->directoryIdCalculator->calculateDirectoryId($resource) . '/' . $resource->getId();
    }

    /**
     * @return string
     */
    private function getPrefix()
    {
        return (!$this->prefix) ? '' : trim($this->prefix, '/') . '/';
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

        $path .= $this->directoryIdCalculator->calculateDirectoryId($file ?: $resource) . '/' . $version->toString();
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
