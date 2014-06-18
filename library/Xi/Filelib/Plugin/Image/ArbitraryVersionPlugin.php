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
use Xi\Filelib\InvalidArgumentException;
use Xi\Filelib\Plugin\VersionProvider\InvalidVersionException;
use Xi\Filelib\Plugin\VersionProvider\LazyVersionProvider;
use Xi\Filelib\FileLibrary;
use Closure;
use Xi\Filelib\Plugin\VersionProvider\Version;

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

    private $paramsValidityChecker;

    private $defaultParamsGetter;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @param string $identifier
     * @param array $commandDefinitions
     * @param string $mimeType
     */
    public function __construct(
        $identifier,
        \Closure $defaultParamsGetter,
        \Closure $paramsValidityChecker,
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
        $this->defaultParamsGetter = $defaultParamsGetter;
        $this->paramsValidityChecker = $paramsValidityChecker;
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
    public function createAllTemporaryVersions(File $file)
    {
        return array(
            $this->identifier => $this->createTemporaryVersion($file, $this->identifier)
        );
    }

    public function createTemporaryVersion(File $file, Version $version)
    {
        if (!$this->isValidVersion($version)) {
            throw new InvalidVersionException('Invalid version');
        }

        $retrieved = $this->storage->retrieve(
            $file->getResource()
        );

        $commandDefinitions = call_user_func_array(
            $this->commandDefinitionsGetter,
            array(
                $file,
                $this->getParams($version)
            )
        );

        $vpv = new VersionPluginVersion(
            $version,
            $commandDefinitions,
            $this->mimeType
        );

        $img = $vpv->getHelper()->createImagick($retrieved);
        $vpv->getHelper()->execute($img);
        $tmp = $this->tempDir . '/' . uniqid('', true);
        $img->writeImage($tmp);

        return $tmp;
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
    public function getExtension(File $file, Version $version)
    {
        if ($this->mimeType) {
            return $this->getExtensionFromMimeType($this->mimeType);
        }
        return parent::getExtension($file, $version);
    }

    public function isSharedResourceAllowed()
    {
        return false;
    }

    public function areSharedVersionsAllowed()
    {
        return false;
    }

    public function isValidVersion(Version $version)
    {
        if (!in_array(
            $version->getVersion(),
            $this->getProvidedVersions()
        )) return false;

        return call_user_func_array($this->paramsValidityChecker, array($this->getParams($version)));
    }

    private function getParams(Version $version)
    {
        return $version->getParams() ?: call_user_func($this->defaultParamsGetter);
    }

}
