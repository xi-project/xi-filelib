<?php

namespace Xi\Filelib\File\TypeResolver;

use \Xi\Filelib\File\FileObject;

/**
 * Description of TypeResolver
 *
 * @author pekkis
 */
interface TypeResolver
{

    public function resolveType(FileObject $file);
    
    
}
