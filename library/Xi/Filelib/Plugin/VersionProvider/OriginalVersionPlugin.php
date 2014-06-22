<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\VersionProvider;

use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Version;

/**
 * Mirrors the original file as a version
 */
class OriginalVersionPlugin extends VersionProvider
{
    /**
     * @var string
     */
    private $tempDir;

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
        $this->tempDir = $filelib->getTempDir();
    }

    protected function doCreateAllTemporaryVersions(File $file)
    {
        $retrieved = $this->storage->retrieve($file->getResource());
        $tmp = $this->tempDir . '/' . uniqid('', true);

        copy($retrieved, $tmp);

        return array(
            $this->identifier => $tmp,
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

    public function isValidVersion(Version $version)
    {
        return ($version->toString() === $this->identifier);
    }

}
