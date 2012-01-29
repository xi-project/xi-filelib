<?php

namespace Xi\Tests\Filelib\File\TypeResolver;

use Xi\Filelib\File\TypeResolver\SymfonyTypeResolver;

class SymfonyTypeResolverTest extends TestCase
{
    public function setUp()
    {
        if (!class_exists('Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser')) {
            $this->markTestSkipped('Symfony MimeTypeGuesser not loadable');
        }
        
        parent::setUp();
        $this->resolver = new SymfonyTypeResolver();
    }
    
}
