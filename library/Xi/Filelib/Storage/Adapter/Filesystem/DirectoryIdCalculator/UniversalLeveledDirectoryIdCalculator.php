<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter\Filesystem\DirectoryIdCalculator;

use Xi\Filelib\Versionable;

/**
 * Creates directories in a leveled hierarchy based on a numeric file id
 *
 */
class UniversalLeveledDirectoryIdCalculator implements DirectoryIdCalculator
{
    /**
     * @see DirectoryIdCalculator::calculateDirectoryId
     */
    public function calculateDirectoryId(Versionable $obj)
    {
        $uuid = str_replace('-', '', $obj->getUuid());

        $dirs = [];

        for ($x = 0; $x < strlen($uuid); $x += 2) {
            $dirs[] = substr($uuid, $x, 2);
        }

        return implode(DIRECTORY_SEPARATOR, $dirs);
    }
}
