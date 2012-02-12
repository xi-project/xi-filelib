<?php

namespace Xi\Tests\Filelib\Plugin\Image;

use Imagick;
use Xi\Filelib\Plugin\Image\ChangeFormatPlugin;

class VersionPluginTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Plugin\Image\VersionPlugin'));
        $this->assertArrayHasKey('Xi\Filelib\Plugin\Image\AbstractImagePlugin', class_parents('Xi\Filelib\Plugin\Image\VersionPlugin'));

    }
    
    
}