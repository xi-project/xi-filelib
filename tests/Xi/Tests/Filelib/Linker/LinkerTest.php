<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Linker;

use Xi\Tests\Filelib\TestCase;

class LinkerTest extends TestCase
{
    
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Linker\Linker'));
    }
    
    
}