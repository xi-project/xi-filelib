<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

use Xi\Filelib\File\MimeType;
use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Events;
use Xi\Filelib\File\Upload\FileUpload;

/**
 * Changes images' formats before uploading
 *
 * @author pekkis
 */
class ChangeFormatPlugin extends AbstractPlugin
{
    /**
     * @var array
     */
    protected static $subscribedEvents = array(
        Events::PROFILE_AFTER_ADD => 'onFileProfileAdd',
        Events::FILE_UPLOAD => 'beforeUpload'
    );

    /**
     * @var ImageMagickHelper
     */
    protected $helper;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @param string $targetExtension
     * @param array $commandDefinitions
     */
    public function __construct(array $commandDefinitions = array())
    {
        $this->helper = new ImageMagickHelper($commandDefinitions);
    }

    /**
     * @param FileUploadEvent $event
     */
    public function beforeUpload(FileUploadEvent $event)
    {
        if (!$this->hasProfile($event->getProfile()->getIdentifier())) {
            return;
        }

        $upload = $event->getFileUpload();
        if (!preg_match("/^image/", $upload->getMimeType())) {
            return;
        }

        $img = $this->helper->createImagick($upload->getRealPath());
        $this->helper->execute($img);

        $tempnam = $this->tempDir . '/' . uniqid('cfp', true);
        $img->writeImage($tempnam);

        $pinfo = pathinfo($upload->getUploadFilename());

        $nupload = new FileUpload($tempnam);
        $nupload->setTemporary(true);

        $extension = array_shift(MimeType::mimeTypeToExtensions($nupload->getMimeType()));
        $nupload->setOverrideFilename($pinfo['filename'] . '.' . $extension);

        $event->setFileUpload($nupload);
    }

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        $this->tempDir = $filelib->getTempDir();
    }
}
