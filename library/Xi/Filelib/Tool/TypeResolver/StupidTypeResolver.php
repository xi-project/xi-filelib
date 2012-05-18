<?php

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
