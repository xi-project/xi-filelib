<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Linker;

use Xi\Tests\Filelib\TestCase;

/**
 * @group linker
 */
class AbstractLinkerTest extends TestCase
{
    /**
     * @test
     */
    public function implementsLinker()
    {
        $this->assertContains(
            'Xi\Filelib\Linker\Linker',
            class_implements('Xi\Filelib\Linker\AbstractLinker')
        );
    }
}
