<?php

namespace Xi\Tests\Filelib\Linker;

use Xi\Filelib\Linker\AbstractLinker;
use Xi\Tests\Filelib\TestCase;

class AbstractLinkerTest extends TestCase
{
    
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $linker = $this->getMockBuilder('Xi\Filelib\Linker\AbstractLinker')
                    ->setMethods(array('getLink', 'getLinkVersion'))
                    ->getMockForAbstractClass();
        
        $this->assertNull($linker->getFilelib());
                        
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        
        $this->assertSame($linker, $linker->setFilelib($filelib));
        
        $this->assertSame($filelib, $linker->getFilelib());
    }
    
    /**
     * @test
     */
    public function initShouldReturnSelf()
    {

        $linker = $this->getMockBuilder('Xi\Filelib\Linker\AbstractLinker')
                    ->setMethods(array('getLink', 'getLinkVersion'))
                    ->getMockForAbstractClass();
        
        $this->assertSame($linker, $linker->init());
        
    }
    
    
    
}
