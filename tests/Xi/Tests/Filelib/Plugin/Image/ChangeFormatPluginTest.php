<?php

namespace Xi\Tests\Filelib\Plugin\Image;

use Imagick;
use Xi\Filelib\Plugin\Image\ChangeFormatPlugin;

class ChangeFormatPluginTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Plugin\Image\ChangeFormatPlugin'));
        $this->assertArrayHasKey('Xi\Filelib\Plugin\AbstractPlugin', class_parents('Xi\Filelib\Plugin\Image\ChangeFormatPlugin'));
        
    }
    
    
    
    
}