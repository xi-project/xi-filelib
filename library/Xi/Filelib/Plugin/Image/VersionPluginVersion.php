<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

class VersionPluginVersion
{
    /**
     * @var ImageMagickHelper
     */
    protected $helper;

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
     * @var array
     */
    protected $commandDefinitions;

    /**
     * @param string $identifier
     * @param array $commandDefinitions
     * @param string $mimeType
     */
    public function __construct(
        $identifier,
        array $commandDefinitions = array(),
        $mimeType = null
    ) {
        $this->mimeType = $mimeType;
        $this->identifier = $identifier;
        $this->commandDefinitions = $commandDefinitions;
    }

    /**
     * @return ImageMagickHelper
     */
    public function getHelper($source, $outputDir)
    {
        return new ImageMagickHelper($source, $outputDir, $this->commandDefinitions);
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return null|string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }
}
