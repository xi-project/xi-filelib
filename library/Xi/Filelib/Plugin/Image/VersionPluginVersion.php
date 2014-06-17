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
     * @param string $identifier
     * @param array $commandDefinitions
     * @param string $mimeType
     */
    public function __construct(
        $identifier,
        array $commandDefinitions = array(),
        $mimeType = null
    ) {
        $this->helper = new ImageMagickHelper($commandDefinitions);
        $this->mimeType = $mimeType;
        $this->identifier = $identifier;
    }

    /**
     * @return ImageMagickHelper
     */
    public function getHelper()
    {
        return $this->helper;
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
