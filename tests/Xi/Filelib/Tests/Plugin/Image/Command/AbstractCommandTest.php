<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image\Command;

use Xi\Filelib\Plugin\Image\ImageMagickHelper;
use Xi\Filelib\Tests\Plugin\Image\TestCase;

/**
 * @group plugin
 */
class AbstractCommandTest extends TestCase
{
    /**
     * @test
     */
    public function addHelperReturnsSelf()
    {
        $helper = new ImageMagickHelper();

        $command = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Image\Command\AbstractCommand');
        $this->assertSame($command, $command->setHelper($helper));
    }

}
