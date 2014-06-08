<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image\Command;
use Xi\Filelib\Plugin\Image\ImageMagickHelper;

/**
 * @author pekkis
 */
abstract class AbstractCommand implements Command
{
    /**
     * @var ImageMagickHelper
     */
    protected $helper;

    /**
     * @param ImageMagickHelper $helper
     * @return Command
     */
    public function setHelper(ImageMagickHelper $helper)
    {
        $this->helper = $helper;
        return $this;
    }
}
