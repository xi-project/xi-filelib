<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\File;
use Xi\Filelib\Storage\Versionable;

/**
 * Abstract storage convenience base class with common methods implemented
 *
 * @author pekkis
 */
abstract class BaseStorageAdapter implements StorageAdapter
{
    public function attachTo(FileLibrary $filelib)
    {
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
        return array($resource, $file);
    }



}
