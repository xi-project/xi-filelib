<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Plugin\VersionProvider\LazyVersionProvider;
use Xi\Filelib\FileLibrary;

/**
 * Versions an image
 */
class VersionPlugin extends LazyVersionProvider
{
    /**
     * @var ImageMagickHelper
     */
    protected $imageMagickHelper;

    /**
     * @var string Mime type of version provided
     */
    protected $mimeType;

    /**
     * @var string
     */
    protected $tempDir;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @param string $identifier
     * @param array $commandDefinitions
     * @param string $mimeType
     */
    public function __construct(
        $identifier,
        $commandDefinitions = array(),
        $mimeType = null
    ) {
        parent::__construct(
            function (File $file) {
                // @todo: maybe some more complex mime type based checking
                return (bool) preg_match("/^image/", $file->getMimetype());
            }
        );
        $this->imageMagickHelper = new ImageMagickHelper($commandDefinitions);
        $this->mimeType = $mimeType;
        $this->identifier = $identifier;
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
     * Creates temporary version
     *
     * @param  File  $file
     * @return array
     */
    public function createTemporaryVersions(File $file)
    {
        $retrieved = $this->storage->retrieve($file->getResource());
        $img = $this->getImageMagickHelper()->createImagick($retrieved);

        $this->getImageMagickHelper()->execute($img);

        $tmp = $this->tempDir . '/' . uniqid('', true);
        $img->writeImage($tmp);

        return array(
            $this->identifier => $tmp
        );
    }

    /**
     * @return array
     */
    public function getProvidedVersions()
    {
        return array($this->identifier);
    }

    /**
     * @param File $file
     * @param string $version
     * @return string
     */
    public function getExtension(File $file, $version)
    {
        if ($this->mimeType) {
            return $this->getExtensionFromMimeType($this->mimeType);
        }
        return parent::getExtension($file, $version);
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
