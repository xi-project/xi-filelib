<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image\Command;

use Imagick;
use ImagickException;
use InvalidArgumentException;
use Xi\Filelib\Configurator;

/**
 * @author pekkis
 */
abstract class AbstractCommand implements Command
{
    /**
     * Creates a new Imagick resource from path
     *
     * @param  string                   $path Image path
     * @return Imagick
     * @throws InvalidArgumentException
     */
    public function createImagick($path)
    {
        try {
            return new Imagick($path);
        } catch (ImagickException $e) {
            throw new InvalidArgumentException(
                sprintf("ImageMagick could not be created from path '%s'", $path),
                500,
                $e
            );
        }
    }
}
