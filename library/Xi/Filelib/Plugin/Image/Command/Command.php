<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image\Command;

use Imagick;

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
     * Creates a new Imagick resource
     *
     * @param  string  $path Image path
     * @return Imagick
     * @todo This is not necessary
     */
    public function createImagick($path);
}
