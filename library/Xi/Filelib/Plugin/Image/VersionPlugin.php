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
use Xi\Filelib\FileLibrary;

/**
 * Versions an image
 */
class VersionPlugin extends AbstractVersionProvider
{
    protected $imageMagickHelper;

    /**
     * @var File extension for the version
     */
    protected $extension;

    /**
     * @var string
     */
    private $tempDir;

    public function __construct(
        $identifier,
        $commandDefinitions = array(),
        $extension = null
    ) {
        parent::__construct(
            $identifier,
            function(File $file) {
                // @todo: maybe some more complex mime type based checking
                return (bool) preg_match("/^image/", $file->getMimetype());
            }
        );
        $this->extension = $extension;

        $this->imageMagickHelper = new ImageMagickHelper($commandDefinitions);
    }

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->tempDir = $filelib->getTempDir();
    }

    /**
     * Returns ImageMagick helper
     *
     * @return ImageMagickHelper
     */
    public function getImageMagickHelper()
    {
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
        $retrieved = $this->getStorage()->retrieve($file->getResource());
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
     * Returns the plugins file extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    public function getExtensionFor(File $file, $version)
    {
        // Hard coded extension (the old way)
        if ($extension = $this->getExtension()) {
            return $extension;
        }
        return parent::getExtensionFor($file, $version);
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
