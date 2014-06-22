<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\VersionProvider;

use Xi\Filelib\Plugin\VersionProvider\OriginalVersionPlugin;
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
        $version = Version::get($version);
        $plugin = new OriginalVersionPlugin('originale');
        $this->assertEquals($expected, $plugin->isValidVersion($version));
    }


}
