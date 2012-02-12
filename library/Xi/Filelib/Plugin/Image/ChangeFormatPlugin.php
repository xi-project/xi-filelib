<?php

namespace Xi\Filelib\Plugin\Image;

use \Imagick;

/**
 * Changes images' formats before uploading them.
 *
 * @author pekkis
 * @package Xi_Filelib
 *
 */
class ChangeFormatPlugin extends AbstractImagePlugin
{
    
    
    /**
     * Sets target file's extension
     *
     * @param string $targetExtension
     */
    public function setTargetExtension($targetExtension)
    {
        $this->_targetExtension = $targetExtension;
    }

    /**
     * Returns target file extension
     *
     * @return string
     */
    public function getTargetExtension()
    {
        return $this->_targetExtension;
    }
    
    
    public function beforeUpload(\Xi\Filelib\File\Upload\FileUpload $upload)
    {
        
        $mimetype = $upload->getMimeType();
        
        // @todo: use filebanksta type detection
        if(!preg_match("/^image/", $mimetype)) {
            return $upload;   
        }
                
        $img = $this->createImagick($upload->getPathname());
        
        $this->execute($img);
        
        $tempnam = $this->getFilelib()->getTempDir() . '/' . uniqid('cfp', true);
        $img->writeImage($tempnam);

        $pinfo = pathinfo($upload->getPathname());

        $nupload = $this->getFilelib()->file()->prepareUpload($tempnam);
        $nupload->setTemporary(true);
        
        $nupload->setOverrideFilename($pinfo['filename'] . '.' . $this->getTargetExtension());

        return $nupload;
    }

}