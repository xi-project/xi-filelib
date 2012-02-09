<?php

namespace Xi\Tests\Filelib\File;

use Xi\Filelib\File\FileObject;

use Xi\Tests\Filelib\TestCase;

class FileObjectTest extends TestCase
{

    /**
     * @test
     */
    public function typeResolverShouldReturnFinfoResolverByDefault()
    {
        $this->assertEquals('Xi\Filelib\File\TypeResolver\FinfoTypeResolver', get_class(FileObject::getTypeResolver()));
    }
    
    
    /**
     * @test
     */
    public function typeResolverShouldObeyStaticSetterAndGetter()
    {
        
        $mockResolver = $this->getMockBuilder('Xi\Filelib\File\TypeResolver\FinfoTypeResolver')->setMockClassName('MockResolver')->getMock();
        
        FileObject::setTypeResolver($mockResolver);
        
        $this->assertEquals('MockResolver', get_class(FileObject::getTypeResolver()));
                
        FileObject::setTypeResolver(new \Xi\Filelib\File\TypeResolver\FinfoTypeResolver());
        
        $this->assertEquals('Xi\Filelib\File\TypeResolver\FinfoTypeResolver', get_class(FileObject::getTypeResolver()));
        
        
    }
    
    /**
     * @test
     */
    public function getMimeTypeShouldDelegateToTypeResolverAndThenCacheTheResult()
    {
        $mockResolver = $this->getMockBuilder('Xi\Filelib\File\TypeResolver\FinfoTypeResolver')->getMock();
        $mockResolver->expects($this->exactly(1))->method('resolveType')->will($this->returnValue('lussen/lus'));

        FileObject::setTypeResolver($mockResolver);
        
        $file = new FileObject(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        
        $mimetype = $file->getMimeType();
        
        $this->assertEquals('lussen/lus', $mimetype);
        
        $mimetype = $file->getMimeType();

        $this->assertEquals('lussen/lus', $mimetype);
        
        
        FileObject::setTypeResolver(new \Xi\Filelib\File\TypeResolver\FinfoTypeResolver());
        
    }
    
    
    
    
    
    
}
