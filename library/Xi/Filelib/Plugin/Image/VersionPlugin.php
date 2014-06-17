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
     * @var string
     */
    protected $tempDir;

    /**
     * @var VersionPluginVersion[]
     */
    protected $versions;


    /**
     * @param string $identifier
     * @param array $commandDefinitions
     * @param string $mimeType
     */
    public function __construct(
        $versionDefinitions = array()
    ) {
        parent::__construct(
            function (File $file) {
                // @todo: maybe some more complex mime type based checking
                return (bool) preg_match("/^image/", $file->getMimetype());
            }
        );

        foreach ($versionDefinitions as $key => $definition) {
            $this->versions[$key] = new VersionPluginVersion(
                $key,
                $definition[0],
                isset($definition[1]) ? $definition[1] : null
            );
        }

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
    public function createTemporaryVersions(File $file)
    {
        $retrieved = $this->storage->retrieve(
            $file->getResource()
        );

        $ret = array();

        foreach ($this->versions as $key => $version) {

            $img = $version->getHelper()->createImagick($retrieved);
            $version->getHelper()->execute($img);
            $tmp = $this->tempDir . '/' . uniqid('', true);
            $img->writeImage($tmp);

            $ret[$version->getIdentifier()] = $tmp;
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function getProvidedVersions()
    {
        $ret = array();
        foreach ($this->versions as $version) {
            $ret[] = $version->getIdentifier();
        }

        return $ret;
    }

    /**
     * @param File $file
     * @param string $version
     * @return string
     */
    public function getExtension(File $file, $version)
    {
        if ($mimeType = $this->versions[$version]->getMimeType()) {
            return $this->getExtensionFromMimeType($mimeType);
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
