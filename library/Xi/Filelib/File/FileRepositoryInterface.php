<?php

namespace Xi\Filelib\File;

use Xi\Filelib\Folder\Folder;

interface FileRepositoryInterface
{
    public function upload($upload, Folder $folder = null, $profile = 'default');

    public function afterUpload(File $file);

}