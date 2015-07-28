<?php

namespace Xi\Filelib\Tests\File;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\File\FileObject;

class FileObjectTest extends TestCase
{
    /**
     * @test
     */
    public function returnsMimeType()
    {
        $fileObj = new FileObject(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        $this->assertEquals('image/jpeg', $fileObj->getMimeType());
    }
}
