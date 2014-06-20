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
use Xi\Filelib\Plugin\VersionProvider\InvalidVersionException;
use Xi\Filelib\Plugin\VersionProvider\LazyVersionProvider;
use Xi\Filelib\FileLibrary;
use Closure;
use Xi\Filelib\Plugin\VersionProvider\Version;
use Xi\Filelib\RuntimeException;
use Xi\Filelib\Storage\Storage;

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
     * @param string $identifier
     * @param array $commandDefinitions
     * @param string $mimeType
     */
    public function __construct(
        $identifier,
        \Closure $defaultParamsGetter,
        \Closure $paramsValidityChecker,
        \Closure $commandDefinitionsGetter,
        $mimeTypeGetter
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

        $this->mimeTypeGetter = $this->createMimeTypeGetter($mimeTypeGetter);

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
                $this->getParams($version),
                $this
            )
        );

        $vpv = new VersionPluginVersion(
            $version,
            $commandDefinitions,
            null
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
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    public function getMimeType(File $file, Version $version)
    {
        if ($mimeType = call_user_func_array($this->mimeTypeGetter, array($file, $version))) {
            return $mimeType;
        }
        throw new RuntimeException("Mime type not definable");
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
        )) {
            return false;
        }

        return call_user_func_array(
            $this->paramsValidityChecker,
            array(
                $this->getParams($version)
            )
        );
    }

    private function getParams(Version $version)
    {
        return array_merge(
            call_user_func($this->defaultParamsGetter),
            $version->getParams()
        );
    }

    private function createMimeTypeGetter($mimeTypeGetter)
    {
        if (is_callable($mimeTypeGetter)) {
            return $mimeTypeGetter;
        }

        return function () use ($mimeTypeGetter) {
            return $mimeTypeGetter;
        };
    }


}
