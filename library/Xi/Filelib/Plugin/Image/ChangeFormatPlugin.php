<?php

namespace Xi\Filelib\Plugin\Image;

use Imagick;
use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Configurator;
use Xi\Filelib\Event\FileUploadEvent;

/**
 * Changes images' formats before uploading them.
 *
 * @author pekkis
 *
 */
class ChangeFormatPlugin extends AbstractPlugin
{
    static protected $subscribedEvents = array(
        'fileprofile.add' => 'onFileProfileAdd',
        'file.beforeUpload' => 'beforeUpload'
    );
    
    protected $imageMagickHelper;
    
    protected $targetExtension;
        
    public function __construct($options = array())
    {
        parent::__construct($options);
        Configurator::setOptions($this->getImageMagickHelper(), $options);
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
        }
        return $this->imageMagickHelper;        
    }
    
    /**
     * Sets target file's extension
     *
     * @param string $targetExtension
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
        if(!preg_match("/^image/", $mimetype)) {
            return;
        }
                
        $img = $this->getImageMagickHelper()->createImagick($upload->getRealPath());
        $this->getImageMagickHelper()->execute($img);
                
        $tempnam = $this->getFilelib()->getTempDir() . '/' . uniqid('cfp', true);
        $img->writeImage($tempnam);

        $pinfo = pathinfo($upload->getUploadFilename());

        $nupload = $this->getFilelib()->getFileOperator()->prepareUpload($tempnam);
        $nupload->setTemporary(true);
        
        $nupload->setOverrideFilename($pinfo['filename'] . '.' . $this->getTargetExtension());

        $event->setFileUpload($nupload);
        
        return $nupload;
    }

}