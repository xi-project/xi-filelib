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
    }

}
