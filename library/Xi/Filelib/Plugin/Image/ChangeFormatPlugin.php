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
class ChangeFormatPlugin extends \Xi\Filelib\Plugin\AbstractPlugin
{
    protected $_commands = array();
    
    protected $_imageMagickOptions = array();
    
    public function addCommand(Command\Command $command)
    {
        $this->_commands[] = $command;
    }
    
    public function getCommands()
    {
        return $this->_commands;
    }
    
    public function setCommands(array $commands = array())
    {
        foreach($commands as $command)
        {
            $command = new $command['type']($command);
            $this->addCommand($command);
        }

    }
   
    
    /**
     * Sets ImageMagick options
     *
     * @param array $imageMagickOptions
     */
    public function setImageMagickOptions($imageMagickOptions)
    {
        $this->_imageMagickOptions = $imageMagickOptions;
    }

    

    /**
     * Return ImageMagick options
     *
     * @return array
     */
    public function getImageMagickOptions()
    {
        return $this->_imageMagickOptions;
    }
    
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
    
    
    public function beforeUpload(\Xi\Filelib\File\FileUpload $upload)
    {
        $mimetype = $upload->getMimeType();
        
        // @todo: use filebanksta type detection
        if(!preg_match("/^image/", $mimetype)) {
            return $upload;   
        }

        $img = new Imagick($upload->getPathname());
        
        foreach($this->getImageMagickOptions() as $key => $value) {
            $method = 'set' . $key;
            $img->$method($value);
        }
        
        
        foreach($this->getCommands() as $command) {
            $command->execute($img);
        }
        
        $tempnam = $this->getFilelib()->getTempDir() . '/' . uniqid('cfp', true);
        $img->writeImage($tempnam);

        $pinfo = pathinfo($upload->getPathname());

        $nupload = $this->getFilelib()->file()->prepareUpload($tempnam);
        $nupload->setTemporary(true);
        
        $nupload->setOverrideFilename($pinfo['filename'] . '.' . $this->getTargetExtension());

        return $nupload;
    }

}