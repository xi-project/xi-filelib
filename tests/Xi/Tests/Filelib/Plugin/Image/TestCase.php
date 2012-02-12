<?php

namespace Xi\Tests\Filelib\Plugin\Image;

use Imagick;

class TestCase extends \Xi\Tests\Filelib\TestCase
{

    public function setUp()
    {
        if (!class_exists('Imagick')) {
            $this->markTestSkipped('ImageMagick extension not loaded');
            return;
        }
    }
    
    
    public function tearDown()
    {
        if (!class_exists('Imagick')) {
            $this->markTestSkipped('ImageMagick extension not loaded');
            return;
        }
    }
    
    
    
}