<?php

namespace Xi\Filelib\Plugin\Image\Command;

use \Imagick;

/**
 * Watermarks an image version
 * 
 * @author pekkis
 *
 */
class WatermarkCommand extends AbstractCommand
{
    /**
     * @var string Watermark image
     */
    protected $_watermarkImage = null;
    
    /**
     * @var string Watermark position
     */
    protected $_watermarkPosition = 'sw';
    
    /**
     * @var integer Watermark padding
     */
    protected $_watermarkPadding = 0;
    
    protected $_watermark;
    
        
    /**
     * Sets watermark image
     * 
     * @param string $image
     */
    public function setWatermarkImage($image)
    {
        $this->_watermarkImage = $image;
    }
    
    /**
     * Returns watermark image
     * 
     * @return string
     */
    public function getWatermarkImage()
    {
        return $this->_watermarkImage;
    }
        
    /**
     * Sets watermark position (nw, ne, se or sw)
     * 
     * @param string $position
     */
    public function setWatermarkPosition($position)
    {
        $this->_watermarkPosition = $position;
    }
    
    
    /**
     * Returns watermark position
     * 
     * @return string
     */
    public function getWatermarkPosition()
    {
        return $this->_watermarkPosition;
    }
    
    
    /**
     * Sets padding for watermark image (in pixels)
     * 
     * @param int $padding
     */
    public function setWatermarkPadding($padding)
    {
        $this->_watermarkPadding = $padding;
    }
    
    /**
     * Returns padding for watermark image (in pixels)
     * 
     * @return integer
     */
    public function getWatermarkPadding()
    {
        return $this->_watermarkPadding;
    }
    
    
    public function execute(Imagick $img)
    {
        if(!$this->getWatermarkImage()) {
            return;
        }
        
        $watermark = $this->_getWatermark(); 
        
        $imageWidth 		= $img->getImageWidth();
	    $imageHeight 		= $img->getImageHeight();

	    $wWidth = $watermark->getImageWidth();
	    $wHeight = $watermark->getImageHeight();

	    
	    switch($this->getWatermarkPosition()) {
	        
	        case 'sw':
                $x = 0 + $this->getWatermarkPadding();
                $y = $imageHeight - $wHeight - $this->getWatermarkPadding();
                break;
                
	        case 'nw':
                $x = 0 + $this->getWatermarkPadding();
                $y = 0 + $this->getWatermarkPadding();
	            break;
	            
	        case 'ne':
                $x = $imageWidth - $wWidth - $this->getWatermarkPadding();
                $y = 0 + $this->getWatermarkPadding();
	            break;
	            
	        case 'se':
	            $y = $imageHeight - $wHeight - $this->getWatermarkPadding();
	            $x = $imageWidth - $wWidth - $this->getWatermarkPadding();
	            break;
	        
	    }
	    
	    
        $img->compositeImage(
		    $watermark,
		    Imagick::COMPOSITE_OVER,
		    $x,
		    $y
		);

		return;
    }
    
    
    protected function _getWatermark()
    {
        if(!$this->_watermark) {
            $this->_watermark = new Imagick($this->getWatermarkImage());
        }
        return $this->_watermark;
    }
  
    
    public function __destruct(){
        if($this->_watermark) {
            $this->_watermark->destroy();
        }
    }
    
    
    
    
}
