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
 * Watermarks an image version
 *
 * @author pekkis
 *
 */
class WatermarkCommand extends AbstractCommand
{

    /**
     * @var string Watermark image
     */
    protected $watermarkImage = null;

    /**
     * @var string Watermark position
     */
    protected $watermarkPosition = 'sw';

    /**
     * @var integer Watermark padding
     */
    protected $watermarkPadding = 0;

    protected $watermark;

    /**
     * Sets watermark image
     *
     * @param string $image
     * @return WatermarkCommand
     */
    public function setWatermarkImage($image)
    {
        $this->watermarkImage = $image;
        return $this;
    }

    /**
     * Returns watermark image
     *
     * @return string
     */
    public function getWatermarkImage()
    {
        return $this->watermarkImage;
    }

    /**
     * Sets watermark position (nw, ne, se or sw)
     *
     * @param string $position
     * @return WatermarkCommand
     */
    public function setWatermarkPosition($position)
    {
        if (!is_string($position)) {
            throw new \InvalidArgumentException("Non-string watermark position");
        }

        if (!in_array($position, array('nw', 'ne', 'sw', 'se'))) {
            throw new \InvalidArgumentException(sprintf("Invalid watermark position '%s'", $position));
        }

        $this->watermarkPosition = $position;
        return $this;
    }

    /**
     * Returns watermark position
     *
     * @return string
     */
    public function getWatermarkPosition()
    {
        return $this->watermarkPosition;
    }

    /**
     * Sets padding for watermark image (in pixels)
     *
     * @param int $padding
     * @return WatermarkCommand
     */
    public function setWatermarkPadding($padding)
    {
        $this->watermarkPadding = $padding;
        return $this;
    }

    /**
     * Returns padding for watermark image (in pixels)
     *
     * @return integer
     */
    public function getWatermarkPadding()
    {
        return $this->watermarkPadding;
    }

    public function execute(Imagick $img)
    {
        $watermark = $this->getWatermarkResource();

        $coordinates = $this->calculateCoordinates($img);

        $img->compositeImage(
                $this->getWatermarkResource(), Imagick::COMPOSITE_OVER, $coordinates['x'], $coordinates['y']
        );

        return;
    }


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
                $y = $imageHeight - $wHeight - $this->getWatermarkPadding();
                $x = $imageWidth - $wWidth - $this->getWatermarkPadding();
                break;
        }

        return array('x' => $x, 'y' => $y);
    }

    /**
     * Returns watermark imagick resource
     *
     * @return Imagick
     */
    public function getWatermarkResource()
    {
        if (!$this->watermark) {
            $this->watermark = $this->createImagick($this->getWatermarkImage());
        }
        return $this->watermark;
    }

    /**
     * Destroys watermark resource if it exists
     */
    public function destroyWatermarkResource()
    {
        if ($this->watermark) {
            $this->watermark->destroy();
        }
    }

    public function __destruct()
    {
        $this->destroyWatermarkResource();
    }

}
