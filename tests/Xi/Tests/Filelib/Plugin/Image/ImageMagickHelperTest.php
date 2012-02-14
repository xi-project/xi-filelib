<?php

namespace Xi\Tests\Filelib\Plugin\Image;

use Imagick;
use ImagickException;
use Xi\Filelib\Plugin\Image\ImageMagickHelper;

class ImageMagickHelperTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Plugin\Image\ImageMagickHelper'));
    }
    
    
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function constructorShouldFailWithNonArrayOptions()
    {
        $options = 'lussuti';
        $helper = new ImageMagickHelper($options);
    }
    
    
    /**
     * @test
     */
    public function constructorShouldPassWithArrayOptions()
    {
        $options = array('lussen' => 'hofer', 'tussen' => 'lussen');
        $helper = new ImageMagickHelper($options);        
    }

    
    /**
     * @test
     */
    public function createImagickShouldReturnNewImagickObject()
    {
        $mock = new ImageMagickHelper();
        
        $imagick = $mock->createImagick(ROOT_TESTS . '/data/self-lussing-manatee.jpg');
        
        $this->assertInstanceOf('\Imagick', $imagick);
    }
    
    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function createImagickShouldFailWithNonExistingFile()
    {
        $mock = new ImageMagickHelper();        
        $imagick = $mock->createImagick(ROOT_TESTS . '/data/illusive-manatee.jpg');
        
        $this->assertInstanceOf('\Imagick', $imagick);
    }
    
    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function createImagickShouldFailWithInvalidFile()
    {
        $mock = new ImageMagickHelper();        
        $imagick = $mock->createImagick(ROOT_TESTS . '/data/20th.wav');
        $this->assertInstanceOf('\Imagick', $imagick);
    }
    
    /*
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $helper = new ImageMagickHelper();
        
        $options = array('lussen' => 'tussen', 'kraa' => 'fuu');
        $this->assertEquals(array(), $helper->getImageMagickOptions());
        $this->assertSame($helper, $helper->setImageMagickOptions($options));
        $this->assertEquals($options, $helper->getImageMagickOptions());
    }
    
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function createCommandFromArrayShouldFailWithNonArray()
    {
        $arr = 'tussi';
        
        $helper = new ImageMagickHelper();
        $helper->createCommandFromArray($arr);
    }
    
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function createCommandFromArrayShouldFailWithArrayWithNoTypeKey()
    {
        $arr = array(
            'luus' => 'tus',
        );
        
        $helper = new ImageMagickHelper();
        $helper->createCommandFromArray($arr);
    }
    
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function createCommandFromArrayShouldFailWithArrayWithNonStringTypeKey()
    {
        $arr = array(
            'luus' => 'tus',
            'type' => 1,
        );
        
        $helper = new ImageMagickHelper();
        $helper->createCommandFromArray($arr);
    }
    
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function createCommandFromArrayShouldFailWithArrayWithInvalidClass()
    {
        $arr = array(
            'luus' => 'tus',
            'type' => 'Xi\Lussen\Tussen\Hofer\Slussen\Slurps',
        );
        
        $helper = new ImageMagickHelper();
        $helper->createCommandFromArray($arr);
    }

    
    
    /**
     * @test
     */
    public function createCommandFromArrayShouldPassWhenAllIsWell()
    {
        $mock = $this->getMockClass('Xi\Filelib\Plugin\Image\Command\WatermarkCommand');
        
        $arr = array(
            'luus' => 'tus',
            'type' => $mock,
        );
        
        $helper = new ImageMagickHelper();
        $command = $helper->createCommandFromArray($arr);
        
        $this->assertInstanceOf($mock, $command);
        
    }

    /**
     * @test
     */
    public function addCommandShouldAddCommand()
    {
        $helper = new ImageMagickHelper(); 
        
        $this->assertEquals(array(), $helper->getCommands());
        
        $mock = $this->getMock('Xi\Filelib\Plugin\Image\Command\WatermarkCommand');
        
        $helper->addCommand($mock);
        
        $commands = $helper->getCommands();
        
        $this->assertEquals(1, count($commands));
        
        $this->assertSame($mock, array_pop($commands));
                        
        $mock2 = $this->getMock('Xi\Filelib\Plugin\Image\Command\WatermarkCommand');
        
        $helper->addCommand($mock2);
        
        $this->assertEquals(2, sizeof($helper->getCommands()));
    }
    
    /**
     * @test
     */
    public function setCommandsShouldIterateAllCommands()
    {
        $helper = $this->getMockBuilder('Xi\Filelib\Plugin\Image\ImageMagickHelper')
                        ->setMethods(array('addCommand'))
                        ->getMock();
                           
        $mock = $this->getMockClass('Xi\Filelib\Plugin\Image\Command\WatermarkCommand');
         
        $arr = array(
            array(
               'luus' => 'tus',
               'type' => $mock,
            ),
            array(
               'luus' => 'tus',
               'type' => $mock,
            ),
        );
        
        $helper->expects($this->exactly(2))->method('addCommand')->with($this->isInstanceOf('Xi\Filelib\Plugin\Image\Command\Command'));
        $helper->setCommands($arr);
    }
    
    /**
     * @test
     */
    public function executeShouldExecuteAllOptionsAndCommandsCorrectly()
    {
        $helper = new ImageMagickHelper();    
        
        $imagick = $this->getMock('Imagick');
        
        $helper->setImageMagickOptions(array(
            'ImageGreenPrimary' => array(6, 66),
            'ImageScene' => 4
        ));
        
        $imagick->expects($this->once())->method('setImageGreenPrimary')->with($this->equalTo(6), $this->equalTo(66));
        $imagick->expects($this->once())->method('setImageScene')->with($this->equalTo(4));
        
        $mock = $this->getMock('Xi\Filelib\Plugin\Image\Command\WatermarkCommand');
        $mock->expects($this->once())->method('execute');

        $mock2 = $this->getMock('Xi\Filelib\Plugin\Image\Command\WatermarkCommand');
        $mock2->expects($this->once())->method('execute');

        $helper->addCommand($mock);
        $helper->addCommand($mock2);
        
        $helper->execute($imagick);
    }
    
    
}