<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File;

use \SplFileObject;
use \Xi\Filelib\Tool\MimeTypeResolver\MimeTypeResolver;
use \Xi\Filelib\Tool\MimeTypeResolver\FinfoMimeTypeResolver;

/**
 * Extends SplFileObject to offer mime type detection via Fileinfo.
 *
 * @author pekkis
 *
 */
class FileObject extends SplFileObject
{
    /**
     * @var string Mimetype
     */
    private $mimeType;

    /**
     * @var TypeResolver
     */
    private static $typeResolver;

    /**
     * Sets type resolver
     *
     * @param TypeResolver $typeResolver
     */
    public static function setMimeTypeResolver(MimeTypeResolver $typeResolver)
    {
        self::$typeResolver = $typeResolver;
    }

    /**
     * Returns type resolver.
     *
     * @return TypeResolver
     */
    public static function getMimeTypeResolver()
    {
        if (!self::$typeResolver) {
            self::$typeResolver = new FinfoMimeTypeResolver();
        }

        return self::$typeResolver;
    }

    /**
     * Returns file's mime type (via type resolver).
     *
     * @return string
     */
    public function getMimeType()
    {
        if (!$this->mimeType) {
            $this->mimeType = self::getMimeTypeResolver()->resolveMimeType($this->getRealPath());
        }
        return $this->mimeType;
    }

}
