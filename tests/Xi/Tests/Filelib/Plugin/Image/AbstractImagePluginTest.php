<?php

namespace Xi\Tests\Filelib\Plugin\Image;

use Imagick;
use Xi\Filelib\Plugin\Image\AbstractPlugin;

class AbstractImagePluginTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Plugin\Image\AbstractImagePlugin'));
        $this->assertArrayHasKey('Xi\Filelib\Plugin\AbstractPlugin', class_parents('Xi\Filelib\Plugin\Image\AbstractImagePlugin'));
        
    }
    
    
    
    
}