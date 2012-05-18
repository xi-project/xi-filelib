<?php

namespace Xi\Filelib\Tool\MimeTypeResolver;

/**
 * Mime type resolver
 *
 */
interface MimeTypeResolver
{
    public function resolveMimeType($path);
}
