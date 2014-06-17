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
use Closure;

/**
 * Versions an image
 */
class ArbitraryVersionPlugin extends LazyVersionProvider
{
    /**
     * @var string
     */
    protected $tempDir;

    private $identifier;

    /**
     * @var Closure
     */
    private $commandDefinitionsGetter;

    private $mimeType;

    /**
     * @param string $identifier
     * @param array $commandDefinitions
     * @param string $mimeType
     */
    public function __construct(
        $identifier,
        \Closure $commandDefinitionsGetter,
        $mimeType
    ) {
        parent::__construct(
            function (File $file) {
                // @todo: maybe some more complex mime type based checking
                return (bool) preg_match("/^image/", $file->getMimetype());
            }
        );

        $this->identifier = $identifier;
        $this->commandDefinitionsGetter = $commandDefinitionsGetter;
        $this->mimeType = $mimeType;

        /*
        foreach ($versionDefinitions as $key => $definition) {
            $this->versions[$key] = new VersionPluginVersion(
                $key,
                $definition[0],
                isset($definition[1]) ? $definition[1] : null
            );
        }
        */

        // The whole plugin does not make any sense if it's not lazy so it self-enables
        $this->enableLazyMode(true);
    }

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->tempDir = $filelib->getTempDir();
    }

    /**
     * Creates temporary version
     *
     * @param  File  $file
     * @return array
     */
    public function createTemporaryVersions(File $file, $requestedVersion = null)
    {
        $retrieved = $this->storage->retrieve(
            $file->getResource()
        );

        $func = $this->commandDefinitionsGetter;
        $commandDefinitions = $func($requestedVersion->getParams());

        $vpv = new VersionPluginVersion(
            $requestedVersion,
            $commandDefinitions,
            $this->mimeType
        );

        $img = $vpv->getHelper()->createImagick($retrieved);
        $vpv->getHelper()->execute($img);
        $tmp = $this->tempDir . '/' . uniqid('', true);
        $img->writeImage($tmp);

        return array(
            $requestedVersion->__toString() => $tmp
        );
    }

    /**
     * @return array
     */
    public function getProvidedVersions()
    {
        return array(
            $this->identifier
        );
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
