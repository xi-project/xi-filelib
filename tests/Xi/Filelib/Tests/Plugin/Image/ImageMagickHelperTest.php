<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image;

use Xi\Filelib\Plugin\Image\ImageMagickHelper;
use Xi\Filelib\Plugin\Image\Command\ExecuteMethodCommand;

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

    /**
     * @test
     */
    public function addCommandShouldAddCommand()
    {
        $helper = new ImageMagickHelper();

        $this->assertEquals(array(), $helper->getCommands());

        $mock = $this->getMock('Xi\Filelib\Plugin\Image\Command\Command');

        $helper->addCommand($mock);

        $commands = $helper->getCommands();

        $this->assertEquals(1, count($commands));

        $this->assertSame($mock, array_pop($commands));

        $mock2 = $this->getMock('Xi\Filelib\Plugin\Image\Command\Command');
        $helper->addCommand($mock2);

        $this->assertEquals(2, sizeof($helper->getCommands()));
    }

    /**
     * @test
     */
    public function executeShouldExecuteAllAddedCommands()
    {
        $helper = new ImageMagickHelper();

        $mock = $this->getMock('Xi\Filelib\Plugin\Image\Command\Command');
        $mock->expects($this->once())->method('execute');

        $mock2 = $this->getMock('Xi\Filelib\Plugin\Image\Command\Command');
        $mock2->expects($this->once())->method('execute');

        $helper->addCommand($mock);
        $helper->addCommand($mock2);

        $imagick = $this->getMock('\Imagick');
        $helper->execute($imagick);
    }

    /**
     * @test
     */
    public function instantiationShouldParseCommandDefinitions()
    {
        $helper = new ImageMagickHelper(
            array(
                array('setImageGreenPrimary', array(6, 66)),
                array('setImageScene', 4),
                'Xi\Filelib\Plugin\Image\Command\WatermarkCommand' => array('lussen', 'se', 5),
                new ExecuteMethodCommand('lussen', 'le tusse'),
            )
        );

        $this->assertCount(4, $helper->getCommands());
    }

    /**
     * @test
     */
    public function replacementOfCommandsShouldWork()
    {
        $first = new ExecuteMethodCommand('setLusso');
        $second = new ExecuteMethodCommand('setGranLusso');
        $third = new ExecuteMethodCommand('setAstroLusso');

        $helper = new ImageMagickHelper(array($first, $second));

        $this->assertSame($second, $helper->getCommand(1));

        $helper->setCommand(1, $third);

        $this->assertSame($third, $helper->getCommand(1));
        $this->assertCount(2, $helper->getCommands());
    }
}
