<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image;

use Imagick;
use Xi\Filelib\Plugin\Image\ArbitraryVersionPlugin;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Resource\ConcreteResource;

/**
 * @group plugin
 */
class ArbitraryVersionPluginTest extends TestCase
{
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
            $this->assertInstanceOf(Version::class, $version);
            return 'lusso/tus';
        };

        $plugin = new ArbitraryVersionPlugin(
            'arbitrage',
            function () { },
            function () { },
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
            function () { },
            function () { },
            function () {
                return null;
            }
        );

        $plugin->getMimeType(File::create(), Version::get('xooxoxx'));
    }

    /**
     * @test
     */
    public function isApplicableToImages()
    {
        $plugin = new ArbitraryVersionPlugin(
            'arbitrage',
            function () { },
            function () { },
            function () { },
            function () { },
            function () { },
            function () {
                return null;
            }
        );
        $plugin->setBelongsToProfileResolver(
            function () {
                return true;
            }
        );

        $imageFile = File::create(
            array(
                'resource' => ConcreteResource::create(
                    array('mimetype' => 'image/lus')
                )
            )
        );

        $videoFile = File::create(
            array(
                'resource' => ConcreteResource::create(
                    array('mimetype' => 'video/lus')
                )
            )
        );

        $this->assertFalse($plugin->isApplicableTo($videoFile));
        $this->assertTrue($plugin->isApplicableTo($imageFile));
    }

    /**
     * @test
     */
    public function attaches()
    {
        $plugin = new ArbitraryVersionPlugin(
            'arbitrage',
            function () { },
            function () { },
            function () { },
            function () { },
            function () { },
            function () {
                return null;
            }
        );

        $storage = $this->getMockedStorage();
        $filelib = $this->getMockedFilelib(
            null,
            array(
                'storage' => $storage
            )
        );

        $plugin->attachTo($filelib);

        $this->assertSame(
            $storage,
            $plugin->getStorage()
        );
    }

    /**
     * @test
     */
    public function sharedResourceIsAllowed()
    {
        $plugin = new ArbitraryVersionPlugin(
            'arbitrage',
            function () { },
            function () { },
            function () { },
            function () { },
            function () { },
            function () {
                return null;
            }
        );
        $this->assertTrue($plugin->isSharedResourceAllowed());
    }

    /**
     * @test
     */
    public function sharedVersionsAreConfigured()
    {
        $plugin = new ArbitraryVersionPlugin(
            'arbitrage',
            function () { },
            function () { },
            function () { },
            function () { },
            function () { },
            function () {
                return null;
            }
        );
        $this->assertTrue($plugin->areSharedVersionsAllowed());

        $plugin = new ArbitraryVersionPlugin(
            'arbitrage',
            function () { },
            function () { },
            function () { },
            function () { },
            function () { },
            function () {
                return null;
            },
            false
        );
        $this->assertFalse($plugin->areSharedVersionsAllowed());
    }

    /**
     * @test
     */
    public function providesSingularVersion()
    {
        $plugin = new ArbitraryVersionPlugin(
            'arbitrage',
            function () { },
            function () { },
            function () { },
            function () { },
            function () { },
            function () {
                return null;
            }
        );
        $this->assertEquals(
            array('arbitrage'),
            $plugin->getProvidedVersions()
        );
    }

    /**
     * @return array
     */
    public function provideVersionsAndValidities()
    {
        return array(
            array('arbitrage', false),
            array('arbitrage::nusutus:lusutus', false),
            array('arbitrage::lussi:xyleeni@x2', true),
            array('arbitrage::lussi:xyleeni@x666', false),
            array('arbitrage::tussi:nus', false),
        );

    }

    /**
     * @test
     * @dataProvider provideVersionsAndValidities
     */
    public function checksVersionValidity($version, $expected)
    {
        if (!$expected) {
            $this->setExpectedException('Xi\Filelib\InvalidVersionException');
        }

        $plugin = new ArbitraryVersionPlugin(
            'arbitrage',
            function () {
                return array('tussi', 'lussi');
            },
            function () {
                return array('x2', 'x5');
            },
            function () {
                return array(
                    'tussi' => 'nusnus'
                );
            },
            function (Version $version) {

                $params = $version->getParams();

                if (!isset($params['lussi'])) {
                    return false;
                }

                return true;
            },
            function () {
                return array();
            },
            function () {
                return 'image/jpeg';
            }
        );

        $version = Version::get($version);
        $version2 = $plugin->ensureValidVersion($version);

        $this->assertNotSame($version, $version2);
        $this->assertInstanceOf(Version::class, $version2);
    }

    /**
     * @test
     */
    public function createsTemporaryVersions()
    {
        $plugin = new ArbitraryVersionPlugin(
            'arbitrary',
            function () {
                return array('xoo');
            },
            function () {
                return array();
            },
            function () {
                return array(
                    'xoo' => 'xuu'
                );
            },
            function () { return true; },
            function () { return array(); },
            function () {
                return 'image/jpeg';
            }
        );

        $resource = ConcreteResource::create(
            array('mimetype' => 'image/jpeg')
        );
        $file = File::create(
            array(
                'resource' => $resource
            )
        );

        $storage = $this->getMockedStorage();
        $storage
            ->expects($this->atLeastOnce())
            ->method('retrieve')
            ->with($resource)
            ->will($this->returnValue(ROOT_TESTS . '/data/self-lussing-manatee.jpg'));

        $filelib = $this->getMockedFilelib(
            null,
            array(
                'tempDir' => ROOT_TESTS . '/data/temp',
                'storage' => $storage
            )
        );

        $plugin->attachTo($filelib);
        $ret = $plugin->createAllTemporaryVersions($file);

        $this->assertCount(1, $ret);
        $this->assertArrayHasKey('arbitrary::xoo:xuu', $ret);
        $this->assertStringStartsWith(ROOT_TESTS . '/data/temp', array_pop($ret));
    }

}
