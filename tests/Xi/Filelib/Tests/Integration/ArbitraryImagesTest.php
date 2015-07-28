<?php

namespace Xi\Filelib\Tests\Integration;

use Imagick;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Plugin\Image\ArbitraryVersionPlugin;
use Xi\Filelib\Plugin\Image\Command\Command;
use Xi\Filelib\Versionable\Version;

class ArbitraryImagesTest extends TestCase
{
    /**
     * @var ArbitraryVersionPlugin
     */
    private $arbitrary;

    public function setUp()
    {
        parent::setUp();

        $arbitraryPlugin = new ArbitraryVersionPlugin(
            'arbitrary',
            function () {
                return array(
                    'x'
                );
            },
            function () {
                return array(
                    'x2'
                );
            },
            function () {
                return array(
                    'x' => 800
                );
            },
            function (Version $version) {

                $params = $version->getParams();

                if (!is_numeric($params['x'])) {
                    return false;
                }

                if ($params['x'] < 200 || $params['x'] > 2000) {
                    return false;
                }

                if ($params['x'] % 100) {
                    return false;
                }

                return true;
            },
            function (File $file, Version $version, ArbitraryVersionPlugin $plugin) {

                $params = $version->getParams();

                if ($version->hasModifier('x2')) {
                    $params['x'] = $params['x'] * 2;
                }

                return Command::createCommandsFromDefinitions(
                    [
                        array('setImageCompression', Imagick::COMPRESSION_JPEG),
                        array('setImageFormat', 'jpg'),
                        array('setImageCompressionQuality', 80),
                        array('cropThumbnailImage', array($params['x'], round($params['x'] / 4))),
                        'Xi\Filelib\Plugin\Image\Command\WatermarkCommand' => array(ROOT_TESTS . '/data/watermark.png', 'se', 10)
                    ]
                );
            },
            'image/jpeg',
            true,
            function () {

                return [
                    [
                        ['x' => 600],
                        []
                    ],
                    [
                        ['x' => 600],
                        ['x2']
                    ],
                ];

            }
        );

        $this->arbitrary = $arbitraryPlugin;

        $this->filelib->addPlugin($arbitraryPlugin);
    }

    /**
     * @test
     */
    public function lazyByDefault()
    {
        $manateePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->uploadFile(new FileUpload($manateePath));

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);

        $resource = $file->getResource();

        $this->assertCount(3, $resource->getVersions());
    }

    /**
     * @test
     */
    public function canBeEagerAndPreCreateSomeVersions()
    {
        $this->arbitrary->enableLazyMode(false);

        $manateePath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';
        $file = $this->filelib->uploadFile(new FileUpload($manateePath));

        $this->assertInstanceOf('Xi\Filelib\File\File', $file);

        $resource = $file->getResource();

        $this->assertCount(5, $resource->getVersions());
    }
}
