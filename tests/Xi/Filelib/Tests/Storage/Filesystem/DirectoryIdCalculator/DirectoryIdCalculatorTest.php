<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Storage\Filesystem\DirectoryIdCalculator;

use Xi\Filelib\Tests\TestCase;

/**
 * @group storage
 */
class DirectoryIdCalculatorTest extends TestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Storage\Filesystem\DirectoryIdCalculator\DirectoryIdCalculator'));
    }
}
