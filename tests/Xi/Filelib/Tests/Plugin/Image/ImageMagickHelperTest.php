<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image;

use Imagick;
use Xi\Filelib\Plugin\Image\ImageMagickHelper;

/**
 * @group plugin
 */
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

        new ImageMagickHelper($options);
    }

    /**
     * @test
     */
    public function constructorShouldPassWithArrayOptions()
    {
        $options = array('lussen' => 'hofer', 'tussen' => 'lussen');

        new ImageMagickHelper($options);
    }

    /**
     * @test
     */
    public function createImagickShouldReturnNewImagickObject()
    {
        $mock = new ImageMagickHelper();

        $imagick = $mock->createImagick(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $this->assertInstanceOf('Imagick', $imagick);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function createImagickShouldFailWithNonExistingFile()
    {
        $mock = new ImageMagickHelper();
        $imagick = $mock->createImagick(ROOT_TESTS . '/data/illusive-manatee.jpg');

        $this->assertInstanceOf('Imagick', $imagick);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function createImagickShouldFailWithInvalidFile()
    {
        $mock = new ImageMagickHelper();
        $imagick = $mock->createImagick(ROOT_TESTS . '/data/20th.wav');
        $this->assertInstanceOf('Imagick', $imagick);
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
     */
    public function addCommandShouldAddCommand()
    {
        $helper = new ImageMagickHelper();

        $this->assertEquals(array(), $helper->getCommands());

        $mock = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\WatermarkCommand')->disableOriginalConstructor()->getMock();

        $helper->addCommand($mock);

        $commands = $helper->getCommands();

        $this->assertEquals(1, count($commands));

        $this->assertSame($mock, array_pop($commands));

        $mock2 = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\WatermarkCommand')->disableOriginalConstructor()->getMock();

        $helper->addCommand($mock2);

        $this->assertEquals(2, sizeof($helper->getCommands()));
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

        $mock = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\WatermarkCommand')->disableOriginalConstructor()->getMock();
        $mock->expects($this->once())->method('execute');

        $mock2 = $this->getMockBuilder('Xi\Filelib\Plugin\Image\Command\WatermarkCommand')->disableOriginalConstructor()->getMock();
        $mock2->expects($this->once())->method('execute');

        $helper->addCommand($mock);
        $helper->addCommand($mock2);

        $helper->execute($imagick);
    }
}
