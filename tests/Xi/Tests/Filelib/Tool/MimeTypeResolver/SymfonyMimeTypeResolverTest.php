<?php

namespace Xi\Tests\Filelib\Tool\MimeTypeResolver;

use Xi\Filelib\Tool\MimeTypeResolver\SymfonyMimeTypeResolver;

class SymfonyTypeResolverTest extends TestCase
{
    public function setUp()
    {
        if (!class_exists('Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser')) {
            $this->markTestSkipped('Symfony MimeTypeGuesser not loadable');
        }

        parent::setUp();
        $this->resolver = new SymfonyMimeTypeResolver();
    }

}
