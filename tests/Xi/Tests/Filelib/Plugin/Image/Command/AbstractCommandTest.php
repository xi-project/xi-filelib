<?php

namespace Xi\Tests\Filelib\Plugin\Image\Command;

use Xi\Tests\Filelib\Plugin\Image\TestCase;


class AbstractCommandTest extends TestCase
{
 
    
    
    /**
     * @test
     * @expectedException PHPUnit_Framework_Error
     */
    public function constructorShouldFailWithNonArrayOptions()
    {
        $options = 'lussuti';
        
        $mock = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\AbstractCommand')
        ->setConstructorArgs($options);
        
    }
    
    
    /**
     * @test
     */
    public function constructorShouldPassWithArrayOptions()
    {
        $options = array('lussen' => 'hofer', 'tussen' => 'lussen');
        
        $mock = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\AbstractCommand')
        ->setConstructorArgs($options);
        
    }
    
    
    /**
     * @test
     */
    public function createImagickShouldReturnNewImagickObject()
    {
        $mock = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\AbstractCommand')->setMethods(array('execute'))
                ->getMock();
        
        $imagick = $mock->createImagick(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        
        $this->assertInstanceOf('\Imagick', $imagick);
        
    }
    
    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function createImagickShouldFailWithNonExistingFile()
    {
        $mock = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\AbstractCommand')->setMethods(array('execute'))
                ->getMock();
        
        $imagick = $mock->createImagick(ROOT_TESTS . '/data/illusive-manatee.jpg');
        
        $this->assertInstanceOf('\Imagick', $imagick);
        
    }
    
    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function createImagickShouldFailWithInvalidFile()
    {
        $mock = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\AbstractCommand')->setMethods(array('execute'))
                ->getMock();
        
        $imagick = $mock->createImagick(ROOT_TESTS . '/data/20th.wav');
        
        $this->assertInstanceOf('\Imagick', $imagick);
        
    }
    
    
    
}
