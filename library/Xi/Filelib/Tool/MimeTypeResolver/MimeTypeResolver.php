<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\MimeTypeResolver;

/**
 * Mime type resolver
 *
 */
interface MimeTypeResolver
{
    public function resolveMimeType($path);
}
