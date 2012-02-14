<?php

namespace Xi\Filelib\Plugin\Image;

use Imagick;
use Xi\Filelib\Plugin\AbstractPlugin;
use Xi\Filelib\Configurator;

/**
 * Changes images' formats before uploading them.
 *
 * @author pekkis
 *
 */
class ChangeFormatPlugin extends AbstractPlugin
{
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
    
    
    public function beforeUpload(\Xi\Filelib\File\Upload\FileUpload $upload)
    {
        $mimetype = $upload->getMimeType();
        // @todo: use filebankstas type detection
        if(!preg_match("/^image/", $mimetype)) {
            return $upload;   
        }
                
        $img = $this->getImageMagickHelper()->createImagick($upload->getPathname());
        $this->getImageMagickHelper()->execute($img);
                
        $tempnam = $this->getFilelib()->getTempDir() . '/' . uniqid('cfp', true);
        $img->writeImage($tempnam);

        $pinfo = pathinfo($upload->getPathname());

        $nupload = $this->getFilelib()->getFileOperator()->prepareUpload($tempnam);
        $nupload->setTemporary(true);
        
        $nupload->setOverrideFilename($pinfo['filename'] . '.' . $this->getTargetExtension());

        return $nupload;
    }

}