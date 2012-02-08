<?php

namespace Xi\Filelib\File\TypeResolver;

use \finfo;
use Xi\Filelib\File\FileObject;


class FinfoTypeResolver implements TypeResolver
{
    
    public function resolveType(FileObject $file)
    {
        $fileinfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileinfo->file($file->getRealPath());
        return $mimeType;
    }
    
    
    
}

