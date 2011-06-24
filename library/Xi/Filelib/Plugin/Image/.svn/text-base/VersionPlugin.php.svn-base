<?php

namespace Xi\Filelib\Plugin\Image;

use \Imagick;

/**
 * Versions an image
 *
 * @author pekkis
 * @package Xi_Filelib
 *
 */
class VersionPlugin extends \Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider
{
    const IMAGEMAGICK_LIFETIME = 5;
    
    protected $_providesFor = array('image');

    protected $_commands = array();
    
    /**
     * @var array Scale options
     */
    protected $_scaleOptions = array();

    
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
     * Creates and stores version
     *
     * @param \Xi_FileItem $file
     */
    public function createVersion(\Xi\Filelib\File\File $file)
    {
        if($this->getFilelib()->file()->getType($file) != 'image') {
            throw new Exception('File must be an image');
        }
   
        // $img = new Imagick($this->getFilelib()->getStorage()->retrieve($file)->getPathname());
        $img = $this->_getImageMagick($file);

        foreach($this->getImageMagickOptions() as $key => $value) {
            $method = 'set' . $key;
            $img->$method($value);
        }
        
        foreach($this->getCommands() as $command) {
            $command->execute($img);
        }
     
        $tmp = $this->getFilelib()->getTempDir() . '/' . uniqid('', true);
        $img->writeImage($tmp);
        
        return $tmp;
    }
    
    
    private function _getImageMagick(\Xi\Filelib\File\File $file)
    {
        static $imageMagicks = array();

        $unixNow = time();
        
        $deletions = array();
        foreach($imageMagicks as $key => $im) {
            if($im['last_access'] < ($unixNow - self::IMAGEMAGICK_LIFETIME)) {
                $deletions[] = $key;
            }
        }
        
        foreach($deletions as $deletion) {
            // \Zend_Debug::dump('deleting poo poo');
            unset($imageMagicks[$key]);
        }
        
        
        if(!isset($imageMagicks[$file->getId()])) {

            $img = new Imagick($this->getFilelib()->getStorage()->retrieve($file)->getPathname());
            
            $imageMagicks[$file->getId()] = array(
                'obj' => $img,
                'last_access' => 0,
            );
            
            
        }

        $imageMagicks[$file->getId()]['last_access'] = $unixNow;
        
        
        return $imageMagicks[$file->getId()]['obj']->clone();
    }


}