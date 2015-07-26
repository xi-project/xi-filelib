<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\VersionProvider;

use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\Version;

class OriginalVersionPluginTest extends TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin');
    }

    /**
     * @test
     */
    public function sharedVersionsAllowed()
    {
        $plugin = new OriginalVersionPlugin();
        $this->assertTrue($plugin->areSharedVersionsAllowed());
    }

    /**
     * @test
     */
    public function sharedResourceAllowed()
    {
        $plugin = new OriginalVersionPlugin();
        $this->assertTrue($plugin->isSharedResourceAllowed());
    }

    /**
     * @return array
     */
    public function provideVersionsAndValidities()
    {
        return array(
            array('originale', true),
            array('originale@x2', false),
            array('originalle', false),
            array('originale::param:value', false),
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

        $plugin = new OriginalVersionPlugin('originale');

        $version = Version::get($version);
        $version2 = $plugin->ensureValidVersion($version);
        $this->assertSame($version, $version2);
        $this->assertInstanceOf('Xi\Filelib\Version', $version2);
    }

    /**
     * @test
     */
    public function isAlwaysApplicable()
    {
        $file = File::create();
        $plugin = new OriginalVersionPlugin();
        $plugin->setBelongsToProfileResolver(
            function () {
                return true;
            }
        );
        $this->assertTrue($plugin->isApplicableTo($file));
    }

    /**
     * @test
     */
    public function createsAllTemporaryVersions()
    {
        $retrievedPath = ROOT_TESTS . '/data/self-lussing-manatee.jpg';

        $file = File::create(array('id' => 1, 'resource' => Resource::create()));
        $storage = $this->getMockedStorage();
        $storage
            ->expects($this->exactly(1))
            ->method('retrieve')
            ->with($this->isInstanceOf('Xi\Filelib\Resource\Resource'))
            ->will($this->returnValue($retrievedPath));

        $ed = $this->getMockedEventDispatcher();
        $pm = $this->getMockedProfileManager(array('xooxer'));
        $filelib = $this->getMockedFilelib(
            null, array(
                'storage' => $storage,
                'pm' => $pm,
                'ed' => $ed
            )
        );

        $plugin = new OriginalVersionPlugin();
        $plugin->attachTo($filelib);
        $ret = $plugin->createAllTemporaryVersions($file);
        $this->assertInternalType('array', $ret);
        $this->assertCount(1, $ret);
        $this->assertArrayHasKey('original', $ret);
        foreach ($ret as $tmp) {
            $this->assertRegExp('#^' . ROOT_TESTS . '/data/temp#', $tmp);
        }
    }

}
