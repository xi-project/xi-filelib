<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File;

use Pekkis\MimeTypes\MimeTypes;
use SplFileObject;

/**
 * Extends SplFileObject to offer mime type detection via Fileinfo.
 *
 * @author pekkis
 *
 */
class FileObject extends SplFileObject
{
    /**
     * Returns file's mime type (via type resolver).
     *
     * @return string
     */
    public function getMimeType()
    {
        return (new MimeTypes())->resolveMimeType($this->getRealPath());
    }
}
