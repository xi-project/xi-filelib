<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Linker;

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
}
