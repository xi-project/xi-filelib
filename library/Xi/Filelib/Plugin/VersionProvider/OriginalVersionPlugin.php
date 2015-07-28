<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\VersionProvider;

use Pekkis\TemporaryFileManager\TemporaryFileManager;
use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\InvalidVersionException;
use Xi\Filelib\Version;

/**
 * Mirrors the original file as a version
 */
class OriginalVersionPlugin extends VersionProvider
{
    /**
     * @var TemporaryFileManager
     */
    private $tempFiles;

    /**
     * @var string
     */
    private $identifier;

    public function __construct(
        $identifier = 'original'
    ) {
        parent::__construct(
            function (File $file) {
                return true;
            }
        );
        $this->identifier = $identifier;
    }

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->tempFiles = $filelib->getTemporaryFileManager();
    }

    protected function doCreateAllTemporaryVersions(File $file)
    {
        return array(
            $this->identifier => $this->tempFiles->addFile($this->storage->retrieve($file->getResource())),
        );
    }

    public function getProvidedVersions()
    {
        return array(
            $this->identifier
        );
    }

    public function isSharedResourceAllowed()
    {
        return true;
    }

    public function areSharedVersionsAllowed()
    {
        return true;
    }

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
