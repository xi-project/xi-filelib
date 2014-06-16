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
}
