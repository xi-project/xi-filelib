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

/**
 * Mirrors the original file as a version
 */
class OriginalVersionPlugin extends AbstractVersionProvider
{
    /**
     * @var string
     */
    private $tempDir;

    public function __construct(
        $identifier
    ) {
        parent::__construct(
            $identifier = 'original',
            function (File $file) {
                return true;
            }
        );
    }

    public function attachTo(FileLibrary $filelib)
    {
        parent::attachTo($filelib);
        $this->tempDir = $filelib->getTempDir();
    }

    /**
     * Creates and stores version
     *
     * @param  File  $file
     * @return array
     */
    public function createTemporaryVersions(File $file)
    {
        $retrieved = $this->storage->retrieve($file->getResource());
        $tmp = $this->tempDir . '/' . uniqid('', true);

        copy($retrieved, $tmp);

        return array(
            $this->getIdentifier() => $tmp,
        );
    }

    public function getVersions()
    {
        return array($this->identifier);
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
