<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Plugin\Image;

use Imagick;

class TestCase extends \Xi\Tests\Filelib\TestCase
{
    public function setUp()
    {
        if (!class_exists('Imagick')) {
            $this->markTestSkipped('ImageMagick extension not loaded');
        }
    }

    public function tearDown()
    {
        if (!class_exists('Imagick')) {
            $this->markTestSkipped('ImageMagick extension not loaded');
        }
    }
}
