<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

use Xi\Filelib\Configurator;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Publisher\Publisher;

/**
 * Versions an image
 */
class VersionPlugin extends AbstractVersionProvider
{
    protected $providesFor = array('image');

    protected $imageMagickHelper;

    /**
     * @var File extension for the version
     */
    protected $extension;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var array
     */
    private $options;

    /**
     * @param  Storage       $storage
     * @param  Publisher     $publisher
     * @param  FileOperator  $fileOperator
     * @param  array         $options
     * @param  string        $tempDir
     * @return VersionPlugin
     */
    public function __construct(Storage $storage, Publisher $publisher,
        FileOperator $fileOperator, $tempDir, array $options = array())
    {
        parent::__construct($storage, $publisher, $fileOperator, $options);

        $this->tempDir = $tempDir;
        $this->options = $options;
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

            Configurator::setOptions($this->imageMagickHelper, $this->options);
        }

        return $this->imageMagickHelper;
    }

    /**
     * Creates and stores version
     *
     * @param  File  $file
     * @return array
     */
    public function createVersions(File $file)
    {
        // Todo: optimize
        $retrieved = $this->getStorage()->retrieve($file->getResource())->getPathname();
        $img = $this->getImageMagickHelper()->createImagick($retrieved);

        $this->getImageMagickHelper()->execute($img);

        $tmp = $this->tempDir . '/' . uniqid('', true);
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
     * @param  string          $extension File extension
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

    /**
     * @return string
     */
    public function getTempDir()
    {
        return $this->tempDir;
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
