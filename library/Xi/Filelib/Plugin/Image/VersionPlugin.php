<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

use Imagick;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider;

/**
 * Versions an image
 *
 */
class VersionPlugin extends AbstractVersionProvider
{

    protected $providesFor = array('image');

    protected $imageMagickHelper;

    /**
     * @var File extension for the version
     */
    protected $extension;

    public function __construct($options = array())
    {
        parent::__construct($options);
        Configurator::setOptions($this->getImageMagickHelper(), $options);
    }

    /**
     * Returns ImageMagick helper
     *
     * @return ImageMagickHelper
     */
    public function getImageMagickHelper()
    {
        if (!$this->imageMagickHelper) {
            $this->imageMagickHelper = new ImageMagickHelper();
        }
        return $this->imageMagickHelper;
    }

    /**
     * Creates and stores version
     *
     * @param File $file
     */
    public function createVersions(File $file)
    {
        // Todo: optimize
        $retrieved = $this->getStorage()->retrieve($file->getResource())->getPathname();
        $img = $this->getImageMagickHelper()->createImagick($retrieved);

        $this->getImageMagickHelper()->execute($img);

        $tmp = $this->getFilelib()->getTempDir() . '/' . uniqid('', true);
        $img->writeImage($tmp);

        return array($this->getIdentifier() => $tmp);
    }

    public function getVersions()
    {
        return array($this->identifier);
    }

    /**
     * Sets file extension
     *
     * @param string $extension File extension
     * @return VersionProvider
     */
    public function setExtension($extension)
    {
        $extension = str_replace('.', '', $extension);
        $this->extension = $extension;
        return $this;
    }

    /**
     * Returns the plugins file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    public function getExtensionFor($version)
    {
        return $this->getExtension();
    }


    public function isSharedResourceAllowed()
    {
        return true;
    }

    public function areSharedVersionsAllowed()
    {
        return true;
    }

}
