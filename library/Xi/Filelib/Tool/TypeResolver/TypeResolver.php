<?php

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
