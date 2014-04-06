<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Profile\FileProfile;
use Xi\Filelib\Folder\Folder;

/**
 * File upload event
 */
class FileUploadEvent extends Event
{
    /**
     * @var FileUpload
     */
    private $fileUpload;

    /**
     * @var Folder
     */
    private $folder;

    /**
     * @var FileProfile
     */
    private $profile;

    public function __construct(FileUpload $fileUpload, Folder $folder, FileProfile $profile)
    {
        $this->fileUpload = $fileUpload;
        $this->folder = $folder;
        $this->profile = $profile;
    }

    /**
     * Returns file upload
     *
     * @return FileUpload
     */
    public function getFileUpload()
    {
        return $this->fileUpload;
    }

    /**
     * Sets file upload
     *
     */
    public function setFileUpload(FileUpload $upload)
    {
        $this->fileUpload = $upload;
    }

    /**
     * Returns profile
     *
     * @return FileProfile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Returns folder
     *
     * @return Folder
     */
    public function getFolder()
    {
        return $this->folder;
    }
}
