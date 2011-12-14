<?php

namespace Xi\Filelib\File;

use \SplFileObject;
use \Xi\Filelib\File\TypeResolver\TypeResolver;
use \Xi\Filelib\File\TypeResolver\FinfoTypeResolver;

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
     *
     * Sets type resolver
     * 
     * @param TypeResolver $typeResolver 
     */
    public static function setTypeResolver(TypeResolver $typeResolver)
    {
        self::$typeResolver = $typeResolver;
    }
    
    /**
     *
     * Returns type resolver.
     * 
     * @return TypeResolver
     */
    public static function getTypeResolver()
    {
        if (!self::$typeResolver) {
            self::$typeResolver = new FinfoTypeResolver();
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
            $this->mimeType = self::getTypeResolver()->resolveType($this);
        }
        return $this->mimeType;
    }
    
    
    
    
}
