<?php

namespace Xi\Filelib\File\TypeResolver;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Xi\Filelib\File\FileObject;

class SymfonyTypeResolver implements TypeResolver
{
    
    private $mimeTypeGuesser;
    
    /**
     *
     * @return MimeTypeGuesser
     */
    private function getMimeTypeGuesser()
    {
        if (!$this->mimeTypeGuesser) {
            $this->mimeTypeGuesser = MimeTypeGuesser::getInstance();
        }
        
        return $this->mimeTypeGuesser;
    }
    
    
    public function resolveType(FileObject $file)
    {
        $mimeType = $this->getMimeTypeGuesser()->guess($file->getRealPath());
        return $mimeType;
    }
    
    
    
}

