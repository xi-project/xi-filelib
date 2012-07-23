<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Plugin;

use Xi\Tests\Filelib\TestCase;

/**
 * @group plugin
 */
class PluginTest extends TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Plugin\Plugin'));
        $this->assertContains('Symfony\Component\EventDispatcher\EventSubscriberInterface', class_implements('Xi\Filelib\Plugin\Plugin'));
    }
}
