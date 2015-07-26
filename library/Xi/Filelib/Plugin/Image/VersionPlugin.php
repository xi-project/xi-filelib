<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

use Pekkis\TemporaryFileManager\TemporaryFileManager;
use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\InvalidVersionException;
use Xi\Filelib\Plugin\VersionProvider\LazyVersionProvider;
use Xi\Filelib\Version;

/**
 * Versions an image
 */
class VersionPlugin extends LazyVersionProvider
{
    /**
     * @var TemporaryFileManager
     */
    protected $tempFiles;

    /**
     * @var VersionPluginVersion[]
     */
    protected $versions;

    /**
     * @param array $versionDefinitions
     */
    public function __construct(
        $versionDefinitions = array()
    ) {
        parent::__construct(
            function (File $file) {
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

    /**
     * @param FileLibrary $filelib
     */
    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->tempFiles = $filelib->getTemporaryFileManager();
    }

    /**
     * @param File $file
     * @param Version $version
     * @return array
     */
    protected function doCreateTemporaryVersion(File $file, Version $version)
    {
        $retrieved = $this->storage->retrieve(
            $file->getResource()
        );

        $versionVersion = $this->versions[$version->getVersion()];

        return array(
            $version->toString(),
            $versionVersion->getHelper($retrieved, $this->tempFiles)->execute(),
        );
    }

    /**
     * @param File $file
     * @return array
     */
    protected function doCreateAllTemporaryVersions(File $file)
    {
        $ret = array();
        foreach ($this->getProvidedVersions() as $version) {
            list ($identifier, $path) = $this->createTemporaryVersion($file, Version::get($version));
            $ret[$identifier] = $path;
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
     * @param Version $version
     * @return string
     */
    public function getExtension(File $file, Version $version)
    {
        if ($mimeType = $this->versions[$version->getVersion()]->getMimeType()) {
            return $this->getExtensionFromMimeType($mimeType);
        }
        return parent::getExtension($file, $version);
    }

    /**
     * @return bool
     */
    public function isSharedResourceAllowed()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function areSharedVersionsAllowed()
    {
        return true;
    }

    /**
     * @param Version $version
     * @return Version
     * @throws InvalidVersionException
     */
    public function ensureValidVersion(Version $version)
    {
        $version = parent::ensureValidVersion($version);

        if (count($version->getParams())) {
            throw new InvalidVersionException("Version has parameters");
        }

        if (count($version->getModifiers())) {
            throw new InvalidVersionException("Version has modifiers");
        }

        return $version;
    }
}
