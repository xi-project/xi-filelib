<?php

namespace Xi\Tests\Filelib\File;

use Xi\Tests\Filelib\TestCase as FilelibTestCase;

class FileOperatorTest extends FilelibTestCase
{
    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(interface_exists('Xi\Filelib\File\FileOperator'));
    }
    
    
}