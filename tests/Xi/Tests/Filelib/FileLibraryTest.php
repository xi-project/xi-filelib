<?php

namespace Xi\Tests\Filelib;

class FileLibraryTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\FileLibrary'));
    }
    
    
}