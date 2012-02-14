<?php

namespace Xi\Filelib\Plugin\Image;

use Imagick;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\File;

/**
 * Versions an image
 *
 * @author pekkis
 * @package Xi_Filelib
 *
 */
class VersionPlugin extends \Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider
{
    
    protected $_providesFor = array('image');

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
     * @param File $file
     */
    public function createVersion(\Xi\Filelib\File\File $file)
    {
        // Todo: optimize
        $retrieved = $this->getFilelib()->getStorage()->retrieve($file)->getPathname();
        $img = $this->getImageMagickHelper()->createImagick($retrieved);

        $this->getImageMagickHelper()->execute($img);
             
        $tmp = $this->getFilelib()->getTempDir() . '/' . uniqid('', true);
        $img->writeImage($tmp);
        
        return $tmp;
    }
    
    


}