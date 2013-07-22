<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File;
use Dflydev\ApacheMimeTypes\PhpRepository;

class MimeType
{
    /**
     * @return PhpRepository
     */
    private static function getRepository()
    {
        static $repository;
        if (!$repository) {
            $repository = new PhpRepository();
        }
        return $repository;
    }

    /**
     * @param string $mimeType
     * @return array
     */
    public static function mimeTypeToExtensions($mimeType)
    {
        return self::getRepository()->findExtensions($mimeType);
    }

    /**
     * @param string $extension
     * @return null|string
     */
    public static function extensionToMimeType($extension)
    {
        return self::getRepository()->findType($extension);
    }
}


