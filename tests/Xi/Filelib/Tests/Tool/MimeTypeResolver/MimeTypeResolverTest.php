<?php

namespace Xi\Filelib\Tests\Tool\MimeTypeResolver;

use Xi\Filelib\Tests\TestCase as FilelibTestCase;

class FileTest extends FilelibTestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\Tool\MimeTypeResolver\MimeTypeResolver'));
    }

}
