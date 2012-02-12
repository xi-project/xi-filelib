<?php

namespace Xi\Filelib\Plugin\Image;

use Imagick;
use Xi\Filelib\Configurator;

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

    protected $imageMagickHelper;
    
    
    
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

        $this->getImageMagickHelper()->execute($img);
             
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
        
        
        return clone $imageMagicks[$file->getId()]['obj'];
    }


}