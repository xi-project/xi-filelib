<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image;

use Xi\Filelib\Plugin\Image\Command\WatermarkCommand;
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
    public function smoke()
    {
        $this->assertClassExists('Xi\Filelib\Plugin\Image\ImageMagickHelper');
    }

    /**
     * @test
     */
    public function initializes()
    {
        $helper = new ImageMagickHelper(
            ROOT_TESTS . '/data/self-lussing-manatee.jpg',
            ROOT_TESTS . '/data/temp'
        );

        $this->assertFalse($helper->isExecuted());
        $this->assertCount(0, $helper->getCommands());
    }

    /**
     * @test
     */
    public function initializesWithCommands()
    {
        $helper = new ImageMagickHelper(
            ROOT_TESTS . '/data/self-lussing-manatee.jpg',
            ROOT_TESTS . '/data/temp',
            array(
                new ExecuteMethodCommand('setImageGreenPrimary', array(6, 66)),
                new ExecuteMethodCommand('setImageScene', array(4)),
                new WatermarkCommand('lussen', 'se', 5),
                new ExecuteMethodCommand('lussen', 'le tusse'),
            )
        );

        $this->assertCount(4, $helper->getCommands());
    }


    /**
     * @test
     */
    public function addCommandShouldAddCommand()
    {
        $helper = new ImageMagickHelper(
            ROOT_TESTS . '/data/self-lussing-manatee.jpg',
            ROOT_TESTS . '/data/temp'
        );

        $this->assertCount(0, $helper->getCommands());

        $mock = $this->getMock('Xi\Filelib\Plugin\Image\Command\Command');
        $mock->expects($this->once())->method('setHelper')->with($helper);

        $helper->addCommand($mock);

        $commands = $helper->getCommands();
        $this->assertCount(1, $commands);

        $this->assertSame($mock, array_pop($commands));

        $mock2 = $this->getMock('Xi\Filelib\Plugin\Image\Command\Command');
        $helper->addCommand($mock2);

        $this->assertCount(2, $helper->getCommands());
    }

    /**
     * @test
     */
    public function executeShouldExecuteAllAddedCommands()
    {
        $helper = new ImageMagickHelper(
            ROOT_TESTS . '/data/self-lussing-manatee.jpg',
            ROOT_TESTS . '/data/temp'
        );

        $mock = $this->getMock('Xi\Filelib\Plugin\Image\Command\Command');
        $mock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Imagick'));

        $mock2 = $this->getMock('Xi\Filelib\Plugin\Image\Command\Command');
        $mock2
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Imagick'));

        $helper->addCommand($mock);
        $helper->addCommand($mock2);

        $ret = $helper->execute();

        $this->assertStringStartsWith(ROOT_TESTS . '/data/temp/', $ret);
        $this->assertFileExists($ret);
    }

    /**
     * @test
     */
    public function doubleExecutionIsForbidden()
    {
        $helper = new ImageMagickHelper(
            ROOT_TESTS . '/data/self-lussing-manatee.jpg',
            ROOT_TESTS . '/data/temp'
        );

        $helper->execute();

        $this->setExpectedException('Xi\Filelib\RuntimeException');
        $helper->execute();
    }

    /**
     * @test
     */
    public function throwsUpWithInvalidSource()
    {
        $helper = new ImageMagickHelper(
            ROOT_TESTS . '/data/illusive-manatee.jpg',
            ROOT_TESTS . '/data/temp'
        );

        $this->setExpectedException('Xi\Filelib\RuntimeException');
        $helper->execute();
    }

    /**
     * @test
     */
    public function replacementOfCommandsShouldWork()
    {
        $first = new ExecuteMethodCommand('setLusso');
        $second = new ExecuteMethodCommand('setGranLusso');
        $third = new ExecuteMethodCommand('setAstroLusso');

        $helper = new ImageMagickHelper(
            ROOT_TESTS . '/data/self-lussing-manatee.jpg',
            ROOT_TESTS . '/data/temp',
            array(
                $first,
                $second
            )
        );

        $this->assertSame($second, $helper->getCommand(1));

        $helper->setCommand(1, $third);

        $this->assertSame($third, $helper->getCommand(1));
        $this->assertCount(2, $helper->getCommands());
    }
}
