<?php

namespace Xi\Filelib\File;

use \SplFileObject, \finfo;

/**
 * Extends SplFileObject to offer mime type detection via Fileinfo.
 *
 * @author pekkis
 *
 */
class FileObject extends SplFileObject
{
    /**
     * @var string Mimetype
     */
    private $mimeType;

    /**
     * Returns file's mime type (via Fileinfo).
     *
     * @return string
     */
    public function getMimeType()
    {
        if (!$this->mimeType) {
            $fileinfo = new finfo(FILEINFO_MIME_TYPE);
            $this->mimeType = $fileinfo->file($this->getRealPath());
        }
        return $this->mimeType;
    }
}
