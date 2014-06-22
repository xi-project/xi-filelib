<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image\Command;

use Imagick;
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\Plugin\Image\ImageMagickHelper;

class WatermarkCommand extends AbstractCommand
{
    /**
     * @var string Watermark image
     */
    protected $image = null;

    /**
     * @var string Watermark position
     */
    protected $position = 'sw';

    /**
     * @var integer Watermark padding
     */
    protected $padding = 0;

    /**
     * @var \Imagick
     */
    protected $watermark = null;

    /**
     * @var ImageMagickHelper
     */
    protected $helper;

    public function __construct($image, $position, $padding)
    {
        $this->image = $image;
        $this->setWatermarkPosition($position);
        $this->padding = $padding;
    }

    /**
     * Returns watermark image
     *
     * @return string
     */
    public function getWatermarkImage()
    {
        return $this->image;
    }

    /**
     * Sets watermark position (nw, ne, se or sw)
     *
     * @param  string                    $position
     * @return WatermarkCommand
     * @throws InvalidArgumentException
     */
    public function setWatermarkPosition($position)
    {
        if (!is_string($position)) {
            throw new InvalidArgumentException("Non-string watermark position");
        }

        if (!in_array($position, array('nw', 'ne', 'sw', 'se'))) {
            throw new InvalidArgumentException(
                sprintf(
                    "Invalid watermark position '%s'",
                    $position
                )
            );
        }

        $this->position = $position;
        return $this;
    }

    /**
     * Returns watermark position
     *
     * @return string
     */
    public function getWatermarkPosition()
    {
        return $this->position;
    }

    /**
     * Returns padding for watermark image (in pixels)
     *
     * @return integer
     */
    public function getWatermarkPadding()
    {
        return $this->padding;
    }

    /**
     * @param Imagick $imagick
     */
    public function execute(Imagick $imagick)
    {
        $coordinates = $this->calculateCoordinates($imagick);

        $imagick->compositeImage(
            $this->getWatermarkResource(),
            Imagick::COMPOSITE_OVER,
            $coordinates['x'],
            $coordinates['y']
        );
    }

    /**
     * @param Imagick $img
     * @return array
     * @throws InvalidArgumentException
     */
    public function calculateCoordinates(Imagick $img)
    {
        $watermark = $this->getWatermarkResource();

        $imageWidth = $img->getImageWidth();
        $imageHeight = $img->getImageHeight();

        $wWidth = $watermark->getImageWidth();
        $wHeight = $watermark->getImageHeight();

        switch ($this->getWatermarkPosition()) {
            case 'sw':
                $x = 0 + $this->getWatermarkPadding();
                $y = $imageHeight - $wHeight - $this->getWatermarkPadding();
                break;
            case 'nw':
                $x = 0 + $this->getWatermarkPadding();
                $y = 0 + $this->getWatermarkPadding();
                break;
            case 'ne':
                $x = $imageWidth - $wWidth - $this->getWatermarkPadding();
                $y = 0 + $this->getWatermarkPadding();
                break;
            case 'se':
            default:
                $y = $imageHeight - $wHeight - $this->getWatermarkPadding();
                $x = $imageWidth - $wWidth - $this->getWatermarkPadding();
                break;
        }

        return array('x' => $x, 'y' => $y);
    }

    /**
     * @return Imagick
     */
    public function getWatermarkResource()
    {
        if (!$this->watermark) {
            $this->watermark = new Imagick($this->getWatermarkImage());
        }

        return $this->watermark;
    }

    public function destroyWatermarkResource()
    {
        if ($this->watermark) {
            $this->watermark->clear();
            $this->watermark = null;
        }
    }

    public function __destruct()
    {
        $this->destroyWatermarkResource();
    }
}
