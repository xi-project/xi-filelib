<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Configurator;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\File\FileOperator;

/**
 * Changes images' formats before uploading them.
 *
 * @author pekkis
 */
class ChangeFormatPlugin extends AbstractPlugin
{
    protected static $subscribedEvents = array(
        'fileprofile.add' => 'onFileProfileAdd',
        'file.beforeUpload' => 'beforeUpload'
    );

    protected $imageMagickHelper;

    protected $targetExtension;

    /**
     * @var FileOperator
     */
    private $fileOperator;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var array
     */
    private $options;

    /**
     * @param  FileOperator       $fileOperator
     * @param  string             $tempDir
     * @param  array              $options
     * @return ChangeFormatPlugin
     */
    public function __construct(FileOperator $fileOperator, $tempDir,
        array $options = array()
    ) {
        parent::__construct($options);

        $this->fileOperator = $fileOperator;
        $this->tempDir = $tempDir;
        $this->options = $options;
    }

    /**
     * Returns ImageMagick helper
     *
     * @return ImageMagickHelper
     */
    public function getImageMagickHelper()
    {
        if (!$this->imageMagickHelper) {
            $this->imageMagickHelper = new ImageMagickHelper();

            Configurator::setOptions($this->imageMagickHelper, $this->options);
        }

        return $this->imageMagickHelper;
    }

    /**
     * Sets target file's extension
     *
     * @param  string             $targetExtension
     * @return ChangeFormatPlugin
     */
    public function setTargetExtension($targetExtension)
    {
        $this->targetExtension = $targetExtension;

        return $this;
    }

    /**
     * Returns target file extension
     *
     * @return string
     */
    public function getTargetExtension()
    {
        return $this->targetExtension;
    }

    public function beforeUpload(FileUploadEvent $event)
    {
        if (!$this->hasProfile($event->getProfile()->getIdentifier())) {
            return;
        }

        $upload = $event->getFileUpload();

        $mimetype = $upload->getMimeType();
        // @todo: use filebankstas type detection
        if (!preg_match("/^image/", $mimetype)) {
            return;
        }

        $img = $this->getImageMagickHelper()->createImagick($upload->getRealPath());
        $this->getImageMagickHelper()->execute($img);

        $tempnam = $this->tempDir . '/' . uniqid('cfp', true);
        $img->writeImage($tempnam);

        $pinfo = pathinfo($upload->getUploadFilename());

        $nupload = $this->fileOperator->prepareUpload($tempnam);
        $nupload->setTemporary(true);

        $nupload->setOverrideFilename($pinfo['filename'] . '.' . $this->getTargetExtension());

        $event->setFileUpload($nupload);

        return $nupload;
    }
}
