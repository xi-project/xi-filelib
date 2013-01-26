<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\MimeTypeResolver;

use \finfo;

class FinfoMimeTypeResolver implements MimeTypeResolver
{

    public function resolveMimeType($path)
    {
        $fileinfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileinfo->file($path);

        return $mimeType;
    }

}
