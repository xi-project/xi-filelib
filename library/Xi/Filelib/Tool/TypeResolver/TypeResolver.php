<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\TypeResolver;

/**
 * Type resolver resolves type from mimetype
 */
interface TypeResolver
{

    /**
     * Resolves type from mime type
     *
     * @return string
     */
    public function resolveType($mimeType);

}
