<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

/**
 * Stores the version of Xi Filelib
 *
 * @author pekkis
 *
 */
final class Version
{
    /**
     * Current Xi Filelib version
     */
    const VERSION = '0.11.0';

    /**
     * Compares a Xi Filelib version with the current one.
     *
     * @param  string $version Xi Filelib version to compare.
     * @return int    Returns -1 if older, 0 if it is the same, 1 if version
     *             passed as argument is newer.
     */
    public static function compare($version)
    {
        $currentVersion = str_replace(' ', '', strtolower(self::VERSION));
        $version = str_replace(' ', '', $version);

        return version_compare($version, $currentVersion);
    }
}
