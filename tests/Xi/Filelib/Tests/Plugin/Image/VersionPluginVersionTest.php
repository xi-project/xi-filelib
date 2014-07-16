<?php

namespace Xi\Filelib\Tests\Plugin\Image;

use Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand;
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

    /**
     * @test
     */
    public function commandsCanBeAdded()
    {
        $version = new VersionPluginVersion('lussominore', [], 'image/lus');

        $this->assertEquals([], $version->getCommands());

        $tussi = new ExecuteMethodCommand('tussi');
        $watussi = new ExecuteMethodCommand('watussi');

        $ret = $version
            ->addCommand($tussi)
            ->addCommand($tussi);

        $this->assertSame($version, $ret);

        $this->assertCount(2, $version->getCommands());
    }

    /**
     * @test
     */
    public function commandsCanBeReplaced()
    {
        $version = new VersionPluginVersion('lussominore', [], 'image/lus');

        $tussi = new ExecuteMethodCommand('tussi');
        $watussi = new ExecuteMethodCommand('watussi');

        $ret = $version->setCommands(
            [
                $tussi,
                $watussi
            ]
        );

        $this->assertSame($version, $ret);
        $this->assertCount(2, $version->getCommands());
        $this->assertSame($watussi, $version->getCommand(1));

        $lussi = new ExecuteMethodCommand('lussi');
        $version->setCommand(1, $lussi);

        $this->assertSame($lussi, $version->getCommand(1));
    }
}
