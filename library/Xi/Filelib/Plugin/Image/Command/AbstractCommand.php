<?php

namespace Xi\Filelib\Plugin\Image\Command;

use Imagick;
use ImagickException;
use InvalidArgumentException;
use Xi\Filelib\Configurator;

/**
 * Abstract convenience class for versionplugin plugins
 *
 * @author pekkis
 *
 */
abstract class AbstractCommand implements Command
{

    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }

    /**
     * Creates a new imagick resource from path
     *
     * @param string $path Image path
     * @return Imagick
     * @throws InvalidArgumentException
     */
    public function createImagick($path)
    {
        try {
            return new Imagick($path);
        } catch (ImagickException $e) {
            throw new InvalidArgumentException(sprintf("ImageMagick could not be created from path '%s'", $path), 500, $e);
        }
    }

}
