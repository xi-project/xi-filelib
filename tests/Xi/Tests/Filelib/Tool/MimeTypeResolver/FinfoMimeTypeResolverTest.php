<?php

namespace Xi\Tests\Filelib\Tool\MimeTypeResolver;

use Xi\Filelib\Tool\MimeTypeResolver\FinfoMimeTypeResolver;

class FinfoMimeTypeResolverTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->resolver = new FinfoMimeTypeResolver();
    }

}
