<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

use Pekkis\TemporaryFileManager\TemporaryFileManager;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\Events;
use Pekkis\MimeTypes\MimeTypes;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\BasePlugin;

/**
 * Changes images' formats before uploading
 *
 * @author pekkis
 */
class ChangeFormatPlugin extends BasePlugin
{
    /**
     * @var array
     */
    protected static $subscribedEvents = array(
        Events::PROFILE_AFTER_ADD => 'onFileProfileAdd',
        Events::FILE_UPLOAD => 'beforeUpload'
    );

    /**
     * @var array
     */
    protected $commandDefinitions;

    /**
     * @var TemporaryFileManager
     */
    private $tempFiles;

    /**
     * @param string $targetExtension
     * @param array $commandDefinitions
     */
    public function __construct(array $commandDefinitions = array())
    {
        $this->commandDefinitions = $commandDefinitions;
    }

    /**
     * @param FileUploadEvent $event
     */
    public function beforeUpload(FileUploadEvent $event)
    {
        if (!$this->belongsToProfile($event->getProfile()->getIdentifier())) {
            return;
        }

        $upload = $event->getFileUpload();
        if (!preg_match("/^image/", $upload->getMimeType())) {
            return;
        }

        $helper = new ImageMagickHelper(
            $upload->getRealPath(),
            $this->tempFiles,
            $this->commandDefinitions
        );
        $tempnam = $helper->execute();

        $pinfo = pathinfo($upload->getUploadFilename());

        $nupload = new FileUpload($tempnam);
        $nupload->setTemporary(true);

        $mimeTypes = new MimeTypes();
        $extension = $mimeTypes->mimeTypeToExtension($nupload->getMimeType());

        $nupload->setOverrideFilename($pinfo['filename'] . '.' . $extension);

        $event->setFileUpload($nupload);
    }

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->tempFiles = $filelib->getTemporaryFileManager();
    }
}
