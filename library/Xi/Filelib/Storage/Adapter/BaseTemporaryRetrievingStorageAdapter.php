<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter;

use Xi\Filelib\FileLibrary;

abstract class BaseTemporaryRetrievingStorageAdapter extends BaseStorageAdapter
{
    /**
     * @var string
     */
    private $tempDir;

    public function attachTo(FileLibrary $filelib)
    {
        $this->tempDir = $filelib->getTempDir();
    }

    /**
     * @return string
     */
    public function getTemporaryFilename()
    {
        return tempnam($this->tempDir, 'str');
    }
}
