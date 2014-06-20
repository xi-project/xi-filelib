<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image;

use Imagick;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\Image\ArbitraryVersionPlugin;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Plugin\VersionProvider\Version;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Events;

/**
 * @group plugin
 */
class ArbitraryVersionPluginTest extends TestCase
{
    /**
     * @var VersionPlugin
     */
    private $plugin;

    /**
     * @var Storage
     */
    private $storage;

    public function setUp()
    {
        if (!class_exists('Imagick')) {
            return $this->markTestSkipped('Imagick required');
        }

        parent::setUp();

        $this->storage = $this->getMockedStorage();
    }

    /**
     * @test
     */
    public function isLazyAtStart()
    {
        $plugin = new ArbitraryVersionPlugin(
            'arbitrage',
            function () { },
            function () { },
            function () { },
            'image/png'
        );

        $this->assertTrue($plugin->lazyModeEnabled());
        $this->assertEquals('image/png', $plugin->getMimeType(File::create(), Version::get('tussi')));
    }

    /**
     * @test
     */
    public function mimeTypeGetterIsDefined()
    {
        $file = File::create();
        $version = Version::get('losofeie');

        $func = function (File $file, Version $version) {
            $this->assertInstanceOf('Xi\Filelib\File\File', $file);
            $this->assertInstanceOf('Xi\Filelib\Plugin\VersionProvider\Version', $version);
            return 'lusso/tus';
        };

        $plugin = new ArbitraryVersionPlugin(
            'arbitrage',
            function () { },
            function () { },
            function () { },
            $func
        );

        $this->assertEquals('lusso/tus', $plugin->getMimeType($file, $version));
    }

    /**
     * @test
     */
    public function throwsUpWhenMimeTypeIsNotGot()
    {
        $this->setExpectedException('Xi\Filelib\RuntimeException');

        $plugin = new ArbitraryVersionPlugin(
            'arbitrage',
            function () { },
            function () { },
            function () { },
            function () {
                return null;
            }
        );

        $plugin->getMimeType(File::create(), Version::get('xooxoxx'));

    }


}
