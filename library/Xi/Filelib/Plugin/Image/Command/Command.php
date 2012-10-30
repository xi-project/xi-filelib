<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image\Command;

use \Imagick;

/**
 * Interface for imagemagick version plugin commands
 *
 * @author pekkis
 *
 */
interface Command
{

    /**
     * Executes command
     */
    public function execute(Imagick $img);

    /**
     * Creates a new imagick resource
     *
     * @param string $path Image path
     * @return Imagick
     */
    public function createImagick($path);
}
