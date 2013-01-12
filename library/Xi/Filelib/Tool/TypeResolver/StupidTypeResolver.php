<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\TypeResolver;

/**
 * Stupid type resolver
 *
 */
class StupidTypeResolver implements TypeResolver
{

    public function resolveType($mimeType)
    {
        $split = explode('/', $mimeType);
        return $split[0];
    }

}
