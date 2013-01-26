<?php

namespace Xi\Filelib\Tests\File;
use Xi\Filelib\Tests\TestCase;
use Xi\Filelib\File\FileObject;

class FileObjectTest extends TestCase
{

    /**
     * @test
     */
    public function mimeTypeResolverShouldReturnFinfoResolverByDefault()
    {
        $resolver = FileObject::getMimeTypeResolver();
        $this->assertEquals('Xi\Filelib\Tool\MimeTypeResolver\FinfoMimeTypeResolver', get_class($resolver));
    }

    /**
     * @test
     */
    public function mimeTypeResolverShouldObeyStaticSetterAndGetter()
    {

        $mockResolver = $this->getMockBuilder('Xi\Filelib\Tool\MimeTypeResolver\FinfoMimeTypeResolver')->setMockClassName('MockResolver')->getMock();

        FileObject::setMimeTypeResolver($mockResolver);

        $this->assertEquals('MockResolver', get_class(FileObject::getMimeTypeResolver()));

        FileObject::setMimeTypeResolver(new \Xi\Filelib\Tool\MimeTypeResolver\FinfoMimeTypeResolver());

        $this->assertEquals('Xi\Filelib\Tool\MimeTypeResolver\FinfoMimeTypeResolver', get_class(FileObject::getMimeTypeResolver()));

    }

    /**
     * @test
     */
    public function getMimeTypeShouldDelegateToTypeResolverAndThenCacheTheResult()
    {
        $mockResolver = $this->getMockBuilder('Xi\Filelib\Tool\MimeTypeResolver\FinfoMimeTypeResolver')->getMock();
        $mockResolver->expects($this->exactly(1))->method('resolveMimeType')->will($this->returnValue('lussen/lus'));

        FileObject::setMimeTypeResolver($mockResolver);

        $file = new FileObject(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $mimetype = $file->getMimeType();

        $this->assertEquals('lussen/lus', $mimetype);

        $mimetype = $file->getMimeType();

        $this->assertEquals('lussen/lus', $mimetype);

        FileObject::setMimeTypeResolver(new \Xi\Filelib\Tool\MimeTypeResolver\FinfoMimeTypeResolver());

    }

}
