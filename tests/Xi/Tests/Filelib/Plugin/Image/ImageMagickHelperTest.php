<?php

namespace Xi\Tests\Filelib\Plugin\Image;

use Imagick;
use Xi\Filelib\Plugin\Image\AbstractPlugin;

class ImageMagickHelperTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Plugin\Image\ImageMagickHelper'));
    }
    
    
    
    
}