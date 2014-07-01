<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image\Command;

use Xi\Filelib\Tests\Plugin\Image\TestCase;
use Xi\Filelib\Plugin\Image\Command\WatermarkCommand;
use Imagick;
use InvalidArgumentException;

/**
 * @group plugin
 */
class WatermarkCommandTest extends TestCase
{

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function setWatermarkPositionShouldFailWithInvalidPosition()
    {
        $command = new WatermarkCommand('tussi', 'lus', 5);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function setWatermarkPositionShouldFailWithNonStringPosition()
    {
        $command = new WatermarkCommand('tussi', new \stdClass, 10);
    }

    public function provideDataForCoordinateCalculation()
    {
        return array(
            array(
                array('x' => 0, 'y' => 0), array(800, 600), array(100, 100), 'nw', 0,
            ),
            array(
                array('x' => 193, 'y' => 15), array(1024, 768), array(100, 20), 'ne', 15,
            ),
            array(
                array('x' => 20, 'y' => 305), array(400, 1000), array(30, 30), 'sw', 20,
            ),
            array(
                array('x' => 158, 'y' => 275), array(800, 600), array(50, 50), 'se', 50,
            ),
            array(
                array('x' => 204, 'y' => 321), array(50, 50), array(100, 10), 'se', 4,
            ),
        );
    }

    /**
     * @test
     * @dataProvider provideDataForCoordinateCalculation
     * @param array   $expected
     * @param array   $imagickO
     * @param array   $watermarkO
     * @param string  $position
     * @param integer $padding
     */
    public function calculateCoordinatesShouldCalculateCoordinatesCorrectly($expected, $imagickO, $watermarkO, $position, $padding)
    {
        $command = new WatermarkCommand(
            ROOT_TESTS . '/data/watermark.png', $position, $padding
        );

        $imagick = new Imagick(ROOT_TESTS . '/data/self-lussing-manatee.jpg');

        $ret = $command->calculateCoordinates($imagick);

        $this->assertEquals($expected, $ret);
    }

    /**
     * @test
     */
    public function getWatermarkShouldReturnImagickResourceAndCacheIt()
    {
        $command = new WatermarkCommand(
            ROOT_TESTS . '/data/watermark.png',
            'nw',
            5
        );

        $cached = $command->getWatermarkResource();
        $this->assertInstanceOf('\Imagick', $cached);

        $res = $command->getWatermarkResource();
        $this->assertSame($cached, $res);
    }

    /**
     * @test
     */
    public function destructClearsWatermarkResource()
    {
        $command = new WatermarkCommand(
            ROOT_TESTS . '/data/watermark.png',
            'nw',
            5
        );

        $res = $command->getWatermarkResource();

        $command->destroyWatermarkResource();

        $this->assertNotSame($res, $command->getWatermarkResource());

        unset($command);
    }


    /**
     * @test
     */
    public function executeShouldExecuteCorrectly()
    {
        $imagick = $this->getMockedImagick(
            ROOT_TESTS . '/data/self-lussing-manatee.jpg'
        );

        $imagick->expects($this->any())->method('getImageWidth')->will($this->returnValue(1024));
        $imagick->expects($this->any())->method('getImageHeight')->will($this->returnValue(768));

        $imagick
            ->expects($this->once())
            ->method('compositeImage')
            ->with(
                $this->isInstanceOf('Imagick'),
                Imagick::COMPOSITE_OVER,
                $this->equalTo(1),
                $this->equalTo(1)
            );


        $command = new WatermarkCommand(ROOT_TESTS . '/data/watermark.png', 'nw', 1);
        $command->execute($imagick);
    }
}
