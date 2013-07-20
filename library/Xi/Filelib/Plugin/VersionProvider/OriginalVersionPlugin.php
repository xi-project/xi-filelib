<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\VersionProvider;

use Xi\Filelib\File\File;

/**
 * Mirrors the original file as a version
 */
class OriginalVersionPlugin extends AbstractVersionProvider
{
    public function __construct(
        $identifier
    ) {
        parent::__construct(
            $identifier,
            function(File $file) {
                return true;
            }
        );
    }

    /**
     * Creates and stores version
     *
     * @param  File  $file
     * @return array
     */
    public function createVersions(File $file)
    {
        return array(
            $this->getIdentifier() => $this->getStorage()->retrieve($file->getResource())
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
