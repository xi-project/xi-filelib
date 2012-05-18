<?php

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

