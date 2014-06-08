<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image\Command;

use Imagick;
use Xi\Filelib\Plugin\Image\ImageMagickHelper;

/**
 * Interface for ImageMagick version plugin commands
 *
 * @author pekkis
 */
interface Command
{
    /**
     * Executes command
     *
     * @param Imagick $imagick
     */
    public function execute(Imagick $imagick);

    /**
     * @param ImageMagickHelper $helper
     * @return Command
     */
    public function setHelper(ImageMagickHelper $helper);
}
