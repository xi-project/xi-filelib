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
use Xi\Filelib\Storage\Storable;

/**
 * Abstract storage convenience base class with common methods implemented
 *
 * @author pekkis
 */
abstract class AbstractStorageAdapter implements StorageAdapter
{
    /**
     * @param Storable $storable
     * @return array Tuple of storage and file (or null)
     */
    protected function extractResourceAndFileFromStorable(Storable $storable)
    {
        if ($storable instanceof File) {
            $file = $storable;
            $resource = $file->getResource();
        } else {
            $resource = $storable;
            $file = null;
        }
        return array($resource, $file);
    }
}
