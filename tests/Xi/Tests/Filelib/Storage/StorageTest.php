<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Storage;

/**
 * @group storage
 */
class StorageTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Storage\Storage'));
    }
}
