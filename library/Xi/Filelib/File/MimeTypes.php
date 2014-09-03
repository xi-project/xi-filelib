<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File;

use Dflydev\ApacheMimeTypes\PhpRepository;

class MimeTypes
{
    /**
     * @var PhpRepository
     */
    private $repository;

    /**
     * @var array
     */
    private $extensionOverrides = [];

    public function __construct()
    {
        // Backwards compatibility and beautifying
        $this->override('jpeg', 'jpg');
    }

    /**
     * @return PhpRepository
     */
    private function getRepository()
    {
        if (!$this->repository) {
            $this->repository = new PhpRepository();
        }
        return $this->repository;
    }

    /**
     * @param string $mimeType
     * @return array
     */
    public function mimeTypeToExtensions($mimeType)
    {
        return $this->getRepository()->findExtensions($mimeType);
    }

    /**
     * @param string $mimeType
     * @return string
     */
    public function mimeTypeToExtension($mimeType)
    {
        $extensions = $this->mimeTypeToExtensions($mimeType);
        $extension = array_shift($extensions);


        return (isset($this->extensionOverrides[$extension])) ? $this->extensionOverrides[$extension] : $extension;
    }

    /**
     * @param string $extension
     * @return null|string
     */
    public function extensionToMimeType($extension)
    {
        return $this->getRepository()->findType($extension);
    }

    /**
     * @param string $extension
     * @param string $override
     * @return MimeTypes
     */
    public function override($extension, $override)
    {
        $this->extensionOverrides[$extension] = $override;

        return $this;
    }

    /**
     * @param string $extension
     * @return MimeTypes
     */
    public function removeOverride($extension)
    {
        unset ($this->extensionOverrides[$extension]);
        return $this;
    }
}
