<?php

namespace Xi\Filelib\Tests\Plugin\Image;

use Xi\Filelib\Plugin\Image\VersionPluginVersion;

class VersionPluginVersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function initializes()
    {

        $version = new VersionPluginVersion(
            'lussogrande',
            array(
                array('setImageCompression', \Imagick::COMPRESSION_JPEG),
                array('setImageFormat', 'jpg'),
                array('setImageCompressionQuality', 50),
                array('cropThumbnailImage', array(800, 200)),
                array('sepiaToneImage', 90),
                'Xi\Filelib\Plugin\Image\Command\WatermarkCommand' => array(__DIR__ . '/watermark.png', 'se', 10),
            ),
            'image/jpeg'
        );

        $this->assertEquals('lussogrande', $version->getIdentifier());
        $this->assertEquals('image/jpeg', $version->getMimeType());
    }
}
