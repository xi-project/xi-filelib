<?php

namespace Xi\Tests\Filelib\Tool\TypeResolver;

use Xi\Filelib\Tool\TypeResolver\StupidTypeResolver;

class StupidTypeResolverTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Tool\TypeResolver\StupidTypeResolver'));
        $this->assertContains('Xi\Filelib\Tool\TypeResolver\TypeResolver', class_implements('Xi\Filelib\Tool\TypeResolver\StupidTypeResolver'));
    }


    public function provideMimeTypes()
    {
        return array(
            array('lussen', 'lussen/hofen'),
            array('application', 'application/pdf'),
            array('image', 'image/jpeg'),
            array('image', 'image/gif'),
        );
    }


    /**
     * @test
     * @dataProvider provideMimeTypes
     */
    public function stupidTypeResolverShouldStupidlyResolveType($expected, $mimeType)
    {
        $resolver = new StupidTypeResolver();
        $this->assertEquals($expected, $resolver->resolveType($mimeType));
    }


}

