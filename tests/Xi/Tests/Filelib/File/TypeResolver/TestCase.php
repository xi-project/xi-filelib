<?php

namespace Xi\Tests\Filelib\File\TypeResolver;

use Xi\Tests\Filelib\TestCase as FilelibTestCase;

use Xi\Filelib\File\FileObject;


class TestCase extends FilelibTestCase 
{

    protected $resolver;
    
    public function provideFiles()
    {
        return array(
            array(
                'self-lussing-manatee.jpg', 'image/jpeg',
            ),
            array(
                'refcard.pdf', 'application/pdf',
            ),
            array(
                'dporssi-screenshot.png', 'image/png',
            ),
            array(
                '20th.wav', 'audio/x-wav',
            ),
        );
    }
    
    /**
     * @test
     * @dataProvider provideFiles
     */
    public function resolverShouldResolveCorrectType($path, $expectedType)
    {
        
        $path = ROOT_TESTS . '/data/' . $path;
        $fobj = new FileObject($path);
        
        $this->assertEquals($expectedType, $this->resolver->resolveType($fobj));
        
        
    }
    

    
    
}
