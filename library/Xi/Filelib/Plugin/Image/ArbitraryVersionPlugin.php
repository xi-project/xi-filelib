<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image;

use Closure;
use Pekkis\TemporaryFileManager\TemporaryFileManager;
use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\InvalidVersionException;
use Xi\Filelib\Plugin\VersionProvider\LazyVersionProvider;
use Xi\Filelib\RuntimeException;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Versionable\Version;

/**
 * Versions an image
 */
class ArbitraryVersionPlugin extends LazyVersionProvider
{
    /**
     * @var TemporaryFileManager
     */
    protected $tempFiles;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var callable
     */
    private $allowedParamsGetter;

    /**
     * @var callable
     */
    private $allowedModifiersGetter;

    /**
     * @var callable
     */
    private $commandDefinitionsGetter;

    /**
     * @var callable
     */
    private $versionValidityChecker;

    /**
     * @var callable
     */
    private $defaultParamsGetter;

    /**
     * @var bool
     */
    private $allowSharedVersions;

    /**
     * @var callable
     */
    private $eagerCreationParamsGetter;

    /**
     * @param callable $identifier
     * @param callable $allowedParamsGetter
     * @param callable $allowedModifiersGetter
     * @param callable $defaultParamsGetter
     * @param callable $versionValidityChecker
     * @param callable $commandDefinitionsGetter
     * @param string|callable $mimeTypeGetter
     * @param bool $allowSharedVersions
     */
    public function __construct(
        $identifier,
        \Closure $allowedParamsGetter,
        \Closure $allowedModifiersGetter,
        \Closure $defaultParamsGetter,
        \Closure $versionValidityChecker,
        \Closure $commandDefinitionsGetter,
        $mimeTypeGetter,
        $allowSharedVersions = true,
        $eagerCreationParamsGetter = null
    ) {
        parent::__construct(
            function (File $file) {
                return (bool) preg_match("/^image/", $file->getMimetype());
            }
        );
        $this->identifier = $identifier;
        $this->allowedParamsGetter = $allowedParamsGetter;
        $this->allowedModifiersGetter = $allowedModifiersGetter;
        $this->defaultParamsGetter = $defaultParamsGetter;
        $this->versionValidityChecker = $versionValidityChecker;
        $this->commandDefinitionsGetter = $commandDefinitionsGetter;
        $this->mimeTypeGetter = $this->createMimeTypeGetter($mimeTypeGetter);
        $this->allowSharedVersions = $allowSharedVersions;
        $this->enableLazyMode(true);

        if (!$eagerCreationParamsGetter) {
            $eagerCreationParamsGetter = function () {
                return [
                    [
                        call_user_func($this->defaultParamsGetter),
                        []
                    ],
                ];
            };
        }
        $this->eagerCreationParamsGetter = $eagerCreationParamsGetter;
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
     * @return array
     */
    protected function doCreateAllTemporaryVersions(File $file)
    {
        $eagerCreationParams = call_user_func($this->eagerCreationParamsGetter);

        $ret = [];
        foreach ($eagerCreationParams as $paramsAndModifiers) {
            list ($params, $modifiers) = $paramsAndModifiers;
            list ($identifier, $path) = $this->doCreateTemporaryVersion(
                $file,
                new Version($this->identifier, $params, $modifiers)
            );
            $ret[$identifier] = $path;
        }
        return $ret;
    }

    /**
     * @param File $file
     * @param Version $version
     * @return array
     */
    protected function doCreateTemporaryVersion(File $file, Version $version)
    {
        $version = $this->ensureValidVersion($version);

        $retrieved = $this->storage->retrieve(
            $file->getResource()
        );

        $commandDefinitions = call_user_func_array(
            $this->commandDefinitionsGetter,
            array(
                $file,
                $version,
                $this
            )
        );

        $helper = new ImageMagickHelper($retrieved, $this->tempFiles, $commandDefinitions);

        return array(
            $version->toString(),
            $helper->execute()
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
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param File $file
     * @param Version $version
     * @return string
     * @throws RuntimeException
     */
    public function getMimeType(File $file, Version $version)
    {
        if ($mimeType = call_user_func_array($this->mimeTypeGetter, array($file, $version))) {
            return $mimeType;
        }
        throw new RuntimeException("Mime type not definable");
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
        return $this->allowSharedVersions;
    }

    /**
     * @param Version $version
     * @return Version
     * @throws InvalidVersionException
     */
    public function ensureValidVersion(Version $version)
    {
        $version = parent::ensureValidVersion($version);

        $unknownParams = array_map(
            function ($param) {
                return '\'' . $param . '\'';
            },
            array_diff(
                array_keys($version->getParams()),
                call_user_func($this->allowedParamsGetter)
            )
        );

        if (count($unknownParams)) {
            throw new InvalidVersionException(
                sprintf(
                    "Unknown version parameters: %s",
                    implode(', ', $unknownParams)
                )
            );
        }

        $unknownModifiers = array_map(
            function ($param) {
                return '\'' . $param . '\'';
            },
            array_diff(
                $version->getModifiers(),
                call_user_func($this->allowedModifiersGetter)
            )
        );

        if (count($unknownModifiers)) {
            throw new InvalidVersionException(
                sprintf(
                    "Unknown version modifiers: %s",
                    implode(', ', $unknownModifiers)
                )
            );
        }

        $newParams = array_merge(
            call_user_func($this->defaultParamsGetter),
            $version->getParams()
        );

        $version = new Version(
            $version->getVersion(),
            $newParams,
            $version->getModifiers()
        );

        $isValid = call_user_func_array(
            $this->versionValidityChecker,
            array(
                $version
            )
        );

        if (!$isValid) {
            throw new InvalidVersionException(
                sprintf(
                    "Invalid version '%s'",
                    $version->toString()
                )
            );
        }

        return $version;
    }

    /**
     * @param mixed $mimeTypeGetter
     * @return callable
     */
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
